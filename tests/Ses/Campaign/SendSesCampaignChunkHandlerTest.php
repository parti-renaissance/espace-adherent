<?php

declare(strict_types=1);

namespace Tests\App\Ses\Campaign;

use App\AdherentMessage\MailchimpStatusEnum;
use App\AdherentMessage\Variable\Parser as VariableParser;
use App\AdherentMessage\Variable\Renderer\SesVariableRenderer;
use App\Doctrine\Utils\BulkInsertHelper;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageReach;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Entity\AdherentMessage\MailchimpStaticSegmentMember;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Mailer\Template\Manager;
use App\Membership\ActivityPositionsEnum;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Repository\AdherentRepository;
use App\Repository\MailchimpCampaignRepository;
use App\Ses\Campaign\Handler\SendSesCampaignChunkHandler;
use App\Ses\Campaign\Message\SendSesCampaignChunkMessage;
use App\Ses\Campaign\Reach\CampaignReachInserter;
use App\Ses\Client\SesEmail;
use App\Ses\Client\SesEmailClient;
use App\Ses\Client\SesSendOutcome;
use App\Ses\Rendering\SesMessageAssembler;
use App\Ses\Rendering\SesRecipientContextFactory;
use App\Ses\Rendering\SesRecipientEmailFactory;
use Doctrine\ORM\EntityManagerInterface;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class SendSesCampaignChunkHandlerTest extends AbstractKernelTestCase
{
    private int $seq = 0;

    public function testSendsChunkRowsMarksSentCompletesCampaignAndRecordsReach(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sending;
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        $client = $this->createMock(SesEmailClient::class);
        $client
            ->expects(self::exactly(2))
            ->method('sendEmail')
            ->willReturnCallback(static fn (SesEmail $email): SesSendOutcome => SesSendOutcome::sent('ses-msg-'.$email->to))
        ;

        $this->createHandler($client)(new SendSesCampaignChunkMessage($campaign->getId(), 1));

        $segmentId = $campaign->getMailchimpStaticSegment()->id;
        $messageId = $campaign->getMessage()->getId();

        self::assertSame(2, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sent));
        self::assertSame(MailchimpStatusEnum::Sent, $this->reloadStatus($campaign));
        self::assertSame(2, $this->countReach($messageId));
    }

    public function testFirstChunkLeavesCampaignSendingSecondChunkCompletesIt(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sending;
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->addMember($campaign, $this->createSubscribedAdherent(), 2, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        $segmentId = $campaign->getMailchimpStaticSegment()->id;
        $messageId = $campaign->getMessage()->getId();

        $client = $this->createMock(SesEmailClient::class);
        $client
            ->expects(self::exactly(2))
            ->method('sendEmail')
            ->willReturnCallback(static fn (SesEmail $email): SesSendOutcome => SesSendOutcome::sent('ses-msg-'.$email->to))
        ;
        $handler = $this->createHandler($client);

        // First chunk: one row left to send in chunk 2, so the campaign stays Sending and reach is not yet recorded.
        $handler(new SendSesCampaignChunkMessage($campaign->getId(), 1));
        self::assertSame(MailchimpStatusEnum::Sending, $this->reloadStatus($campaign));
        self::assertSame(0, $this->countReach($messageId));

        // Last chunk: no sendable row left -> campaign completed and reach recorded once for both rows.
        $handler(new SendSesCampaignChunkMessage($campaign->getId(), 2));
        self::assertSame(MailchimpStatusEnum::Sent, $this->reloadStatus($campaign));
        self::assertSame(2, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sent));
        self::assertSame(2, $this->countReach($messageId));
    }

    public function testAlreadySentRowIsNotResent(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sending;
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Sent);
        $this->manager->flush();

        // Only the Added row is sendable: the SES client is hit exactly once.
        $client = $this->createMock(SesEmailClient::class);
        $client
            ->expects(self::once())
            ->method('sendEmail')
            ->willReturnCallback(static fn (SesEmail $email): SesSendOutcome => SesSendOutcome::sent('ses-msg-'.$email->to))
        ;

        $this->createHandler($client)(new SendSesCampaignChunkMessage($campaign->getId(), 1));

        $segmentId = $campaign->getMailchimpStaticSegment()->id;

        self::assertSame(2, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sent));
        self::assertSame(MailchimpStatusEnum::Sent, $this->reloadStatus($campaign));
    }

    public function testPermanentRejectionMarksRowSentWithoutRethrow(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sending;
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        // A permanent rejection (unverified/invalid address) is terminal: the row is closed (Sent), not
        // reopened, and nothing bubbles up — unlike a transport failure.
        $client = $this->createMock(SesEmailClient::class);
        $client
            ->expects(self::once())
            ->method('sendEmail')
            ->willReturnCallback(static fn (): SesSendOutcome => SesSendOutcome::rejected('Email address is not verified.'))
        ;

        $this->createHandler($client)(new SendSesCampaignChunkMessage($campaign->getId(), 1));

        $segmentId = $campaign->getMailchimpStaticSegment()->id;

        self::assertSame(1, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sent));
        self::assertSame(MailchimpStatusEnum::Sent, $this->reloadStatus($campaign));
    }

    public function testSuppressionListRejectionMarksRecipientHardBounced(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sending;
        $recipient = $this->createSubscribedAdherent();
        $this->addMember($campaign, $recipient, 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();
        $email = $recipient->getEmailAddress();

        // SES refuses a recipient already on the account suppression list: a known-dead mailbox.
        $client = $this->createMock(SesEmailClient::class);
        $client
            ->expects(self::once())
            ->method('sendEmail')
            ->willReturnCallback(static fn (): SesSendOutcome => SesSendOutcome::rejected('Email address is on the suppression list for your account.'))
        ;

        $this->createHandler($client)(new SendSesCampaignChunkMessage($campaign->getId(), 1));

        $this->manager->clear();
        $reloaded = $this->getRepository(Adherent::class)->findOneByEmail($email);
        self::assertTrue($reloaded->isEmailHardBounced(), 'suppression-list rejection must flag the recipient');
    }

    public function testSenderConfigRejectionDoesNotMarkRecipient(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sending;
        $recipient = $this->createSubscribedAdherent();
        $this->addMember($campaign, $recipient, 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();
        $email = $recipient->getEmailAddress();

        // A sender-side config error is not the recipient's fault: live addresses must not be suppressed.
        $client = $this->createMock(SesEmailClient::class);
        $client
            ->expects(self::once())
            ->method('sendEmail')
            ->willReturnCallback(static fn (): SesSendOutcome => SesSendOutcome::rejected('MailFromDomainNotVerified: the sending domain is not verified.'))
        ;

        $this->createHandler($client)(new SendSesCampaignChunkMessage($campaign->getId(), 1));

        $this->manager->clear();
        $reloaded = $this->getRepository(Adherent::class)->findOneByEmail($email);
        self::assertFalse($reloaded->isEmailHardBounced(), 'sender-config rejection must not flag the recipient');
    }

    protected function tearDown(): void
    {
        $this->seq = 0;

        parent::tearDown();
    }

    private function createHandler(SesEmailClient $client): SendSesCampaignChunkHandler
    {
        $memberRepository = self::getContainer()->get(MailchimpStaticSegmentMemberRepository::class);

        return new SendSesCampaignChunkHandler(
            self::getContainer()->get(MailchimpCampaignRepository::class),
            $memberRepository,
            new SesMessageAssembler(self::getContainer()->get(Manager::class)),
            new SesRecipientEmailFactory(new VariableParser(), new SesVariableRenderer(), new SesRecipientContextFactory()),
            $client,
            new CampaignReachInserter($memberRepository, self::getContainer()->get(BulkInsertHelper::class)),
            self::getContainer()->get(AdherentRepository::class),
            self::getContainer()->get(EntityManagerInterface::class),
        );
    }

    private function createCampaign(): MailchimpCampaign
    {
        $author = $this->createSubscribedAdherent();

        $message = new AdherentMessage(null, $author);
        $message->setSubject('Lettre de campagne');
        $message->setContent('<p>Bonjour {{Prénom}}, voici les actualités.</p>');

        $campaign = new MailchimpCampaign($message);
        $segment = new MailchimpStaticSegment($campaign);
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

    private function countByStatus(int $segmentId, SegmentMemberStatusEnum $status): int
    {
        return (int) $this->getRepository(MailchimpStaticSegmentMember::class)
            ->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('IDENTITY(m.staticSegment) = :sid')
            ->andWhere('m.processingStatus = :st')
            ->setParameter('sid', $segmentId)
            ->setParameter('st', $status)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function countReach(int $messageId): int
    {
        return (int) $this->getRepository(AdherentMessageReach::class)
            ->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('IDENTITY(r.message) = :mid')
            ->setParameter('mid', $messageId)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function reloadStatus(MailchimpCampaign $campaign): MailchimpStatusEnum
    {
        $this->manager->clear();

        return $this->getRepository(MailchimpCampaign::class)->find($campaign->getId())->status;
    }

    private function createSubscribedAdherent(): Adherent
    {
        $seq = ++$this->seq;
        $email = \sprintf('ses-chunk-%d@test.dev', $seq);

        // Valid, round-trippable phone: the author is re-hydrated from DB across handler invocations.
        $phone = new PhoneNumber();
        $phone->setCountryCode(33);
        $phone->setNationalNumber('140998211');

        $adherent = Adherent::create(
            Adherent::createUuid($email),
            \sprintf('SES-%d', $seq),
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
