<?php

declare(strict_types=1);

namespace Tests\App\Mailchimp\Campaign\Fallback;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Entity\AdherentMessage\MailchimpStaticSegmentMember;
use App\Entity\AdherentMessage\MandrillFallbackChunk;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Mailchimp\Campaign\Fallback\Handler\TriggerMandrillFallbackHandler;
use App\Mailchimp\Campaign\Fallback\Message\TriggerMandrillFallbackMessage;
use App\Mailchimp\Campaign\MandrillFallbackStatusEnum;
use App\Mailchimp\Driver;
use App\Mailchimp\Manager;
use App\Membership\ActivityPositionsEnum;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Repository\MailchimpCampaignRepository;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Messenger\MessageBusInterface;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class TriggerMandrillFallbackHandlerTest extends AbstractKernelTestCase
{
    private int $seq = 0;

    public function testFanOutDispatchesChunksAndMarksSent(): void
    {
        $campaign = $this->createCampaign(chunksTotal: 2);
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->addMember($campaign, $this->createSubscribedAdherent(), 2, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        $this->handler($this->managerReturning($campaign, ['emails_sent' => 0]), cap: 5000)(new TriggerMandrillFallbackMessage($campaign->getId()));

        self::assertSame(MandrillFallbackStatusEnum::Sent, $this->reloadStatus($campaign));
        self::assertSame(2, $this->countChunkRows($campaign));
    }

    public function testFanOutSkipsChunksWithoutEligibleRecipient(): void
    {
        $campaign = $this->createCampaign(chunksTotal: 3);
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->addMember($campaign, $this->createSubscribedAdherent(), 3, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        $this->handler($this->managerReturning($campaign, ['emails_sent' => 0]), cap: 5000)(new TriggerMandrillFallbackMessage($campaign->getId()));

        self::assertSame(MandrillFallbackStatusEnum::Sent, $this->reloadStatus($campaign));
        // Chunk 2 has no eligible recipient -> only 2 ledger rows are created for chunksTotal=3.
        self::assertSame(2, $this->countChunkRows($campaign));
    }

    public function testEmptyCampaignContentFailsWithoutFanOut(): void
    {
        $campaign = $this->createCampaign(chunksTotal: 1);
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        // Mailchimp returns no rendered HTML -> the fallback must not send a bare/empty body.
        $this->handler($this->managerReturning($campaign, ['emails_sent' => 0]), cap: 5000, campaignContent: '')(new TriggerMandrillFallbackMessage($campaign->getId()));

        self::assertSame(MandrillFallbackStatusEnum::Failed, $this->reloadStatus($campaign));
        self::assertSame(0, $this->countChunkRows($campaign));
    }

    /**
     * HACK(staging-test): asserts the forced-fallback bypass. Remove together with
     * TriggerMandrillFallbackMessage::FORCE_FALLBACK_SUBJECT_TOKEN.
     */
    public function testForcedFallbackViaSubjectTokenIgnoresDeliveredCheckpoint(): void
    {
        $campaign = $this->createCampaign(chunksTotal: 1, subject: 'Test '.TriggerMandrillFallbackMessage::FORCE_FALLBACK_SUBJECT_TOKEN);
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        // Mailchimp reports a full delivery, which normally aborts the fallback (see test above).
        $this->handler($this->managerReturning($campaign, ['emails_sent' => 42]), cap: 5000)(new TriggerMandrillFallbackMessage($campaign->getId()));

        self::assertSame(MandrillFallbackStatusEnum::Sent, $this->reloadStatus($campaign));
        self::assertSame(1, $this->countChunkRows($campaign));
    }

    public function testCheckpointAbortsWhenMailchimpDelivered(): void
    {
        $campaign = $this->createCampaign(chunksTotal: 1);
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        $this->handler($this->managerReturning($campaign, ['emails_sent' => 42]), cap: 5000)(new TriggerMandrillFallbackMessage($campaign->getId()));

        self::assertSame(MandrillFallbackStatusEnum::Aborted, $this->reloadStatus($campaign));
        self::assertSame(0, $this->countChunkRows($campaign));
    }

    public function testCapExceededSkips(): void
    {
        $campaign = $this->createCampaign(chunksTotal: 1);
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        $this->handler($this->managerReturning($campaign, ['emails_sent' => 0]), cap: 1)(new TriggerMandrillFallbackMessage($campaign->getId()));

        self::assertSame(MandrillFallbackStatusEnum::Skipped, $this->reloadStatus($campaign));
        self::assertSame(0, $this->countChunkRows($campaign));
    }

    public function testNoEligibleRecipientFails(): void
    {
        $campaign = $this->createCampaign(chunksTotal: 1);
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Refused);
        $this->manager->flush();

        $this->handler($this->managerReturning($campaign, ['emails_sent' => 0]), cap: 5000)(new TriggerMandrillFallbackMessage($campaign->getId()));

        self::assertSame(MandrillFallbackStatusEnum::Failed, $this->reloadStatus($campaign));
        self::assertSame(0, $this->countChunkRows($campaign));
    }

    public function testAlreadyClaimedIsNoop(): void
    {
        $campaign = $this->createCampaign(chunksTotal: 1);
        $campaign->markFallbackSent();
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        // Claim fails -> the Mailchimp report is never read and nothing is dispatched.
        $manager = $this->createMock(Manager::class);
        $manager->expects(self::never())->method('getReportData');

        $this->handler($manager, cap: 5000)(new TriggerMandrillFallbackMessage($campaign->getId()));

        self::assertSame(MandrillFallbackStatusEnum::Sent, $this->reloadStatus($campaign));
        self::assertSame(0, $this->countChunkRows($campaign));
    }

    private function handler(Manager $manager, int $cap, string $campaignContent = '<html><body>Bonjour *|FNAME|*</body></html>'): TriggerMandrillFallbackHandler
    {
        $driver = $this->createStub(Driver::class);
        $driver->method('getCampaignContent')->willReturn($campaignContent);

        return new TriggerMandrillFallbackHandler(
            self::getContainer()->get(MailchimpCampaignRepository::class),
            $manager,
            self::getContainer()->get(MailchimpStaticSegmentMemberRepository::class),
            self::getContainer()->get(MessageBusInterface::class),
            $this->manager,
            $driver,
            $cap,
        );
    }

    private function managerReturning(MailchimpCampaign $campaign, array $report): Manager&MockObject
    {
        $manager = $this->createMock(Manager::class);
        $manager
            ->expects(self::once())
            ->method('getReportData')
            ->with(self::callback(static fn (MailchimpCampaign $c): bool => $c->getId() === $campaign->getId()))
            ->willReturn($report)
        ;

        return $manager;
    }

    private function reloadStatus(MailchimpCampaign $campaign): ?MandrillFallbackStatusEnum
    {
        $this->manager->clear();

        return $this->getRepository(MailchimpCampaign::class)->find($campaign->getId())->mandrillFallbackStatus;
    }

    private function countChunkRows(MailchimpCampaign $campaign): int
    {
        return (int) $this->getRepository(MandrillFallbackChunk::class)
            ->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('IDENTITY(c.campaign) = :id')
            ->setParameter('id', $campaign->getId())
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function createCampaign(int $chunksTotal, string $subject = 'Sujet'): MailchimpCampaign
    {
        $message = new AdherentMessage();
        $message->setSubject($subject);
        $message->setContent('<p>Bonjour *|FNAME|*</p>');
        $campaign = new MailchimpCampaign($message);
        $campaign->setExternalId('mc-external-id');
        $segment = new MailchimpStaticSegment($campaign);
        $segment->chunksTotal = $chunksTotal;
        $campaign->setMailchimpStaticSegment($segment);

        $this->manager->persist($message);
        $this->manager->persist($campaign);
        $this->manager->persist($segment);

        return $campaign;
    }

    private function addMember(
        MailchimpCampaign $campaign,
        Adherent $adherent,
        int $chunkNumber,
        SegmentMemberStatusEnum $status,
    ): void {
        $member = new MailchimpStaticSegmentMember($campaign->getMailchimpStaticSegment(), $adherent, $chunkNumber);
        $member->processingStatus = $status;

        $this->manager->persist($member);
    }

    private function createSubscribedAdherent(): Adherent
    {
        $seq = ++$this->seq;
        $email = \sprintf('mandrill-trigger-%d@test.dev', $seq);

        $phone = new PhoneNumber();
        $phone->setCountryCode('FR');
        $phone->setNationalNumber('0140998211');

        $adherent = Adherent::create(
            Adherent::createUuid($email),
            \sprintf('MT-%d', $seq),
            $email,
            'super-password',
            'female',
            'Alice',
            'Martin',
            new \DateTime('1990-12-12'),
            ActivityPositionsEnum::STUDENT,
            $this->createPostAddress('92 bld du Général Leclerc', '92110-92024'),
            $phone
        );

        $this->manager->persist($adherent);

        return $adherent;
    }
}
