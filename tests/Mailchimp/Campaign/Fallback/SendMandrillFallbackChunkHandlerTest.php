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
use App\Mailchimp\Campaign\Fallback\Handler\SendMandrillFallbackChunkHandler;
use App\Mailchimp\Campaign\Fallback\MandrillFallbackChunkStatusEnum;
use App\Mailchimp\Campaign\Fallback\Message\SendMandrillFallbackChunkMessage;
use App\Membership\ActivityPositionsEnum;
use App\Repository\AdherentMessage\MandrillFallbackChunkRepository;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class SendMandrillFallbackChunkHandlerTest extends AbstractKernelTestCase
{
    private ?SendMandrillFallbackChunkHandler $handler = null;
    private ?MandrillFallbackChunkRepository $chunkRepository = null;
    private int $seq = 0;

    public function testSendsChunkAndMarksSent(): void
    {
        $campaign = $this->createCampaign();
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->addChunkRow($campaign, 1, MandrillFallbackChunkStatusEnum::Pending);
        $this->manager->flush();

        ($this->handler)(new SendMandrillFallbackChunkMessage($campaign->getId(), 1, '<p>Bonjour *|FNAME|*</p>'));

        self::assertSame(MandrillFallbackChunkStatusEnum::Sent, $this->chunkRepository->findStatus($campaign->getId(), 1));
    }

    public function testAlreadySentChunkIsNoop(): void
    {
        $campaign = $this->createCampaign();
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->addChunkRow($campaign, 1, MandrillFallbackChunkStatusEnum::Sent);
        $this->manager->flush();

        ($this->handler)(new SendMandrillFallbackChunkMessage($campaign->getId(), 1, '<p>Bonjour *|FNAME|*</p>'));

        self::assertSame(MandrillFallbackChunkStatusEnum::Sent, $this->chunkRepository->findStatus($campaign->getId(), 1));
    }

    public function testSendingChunkOnRetryMarksNeedsReview(): void
    {
        $campaign = $this->createCampaign();
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->addChunkRow($campaign, 1, MandrillFallbackChunkStatusEnum::Sending);
        $this->manager->flush();

        ($this->handler)(new SendMandrillFallbackChunkMessage($campaign->getId(), 1, '<p>Bonjour *|FNAME|*</p>'));

        self::assertSame(MandrillFallbackChunkStatusEnum::NeedsReview, $this->chunkRepository->findStatus($campaign->getId(), 1));
    }

    public function testAbortedCampaignSkipsSend(): void
    {
        $campaign = $this->createCampaign();
        $campaign->markFallbackAborted();
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->addChunkRow($campaign, 1, MandrillFallbackChunkStatusEnum::Pending);
        $this->manager->flush();

        ($this->handler)(new SendMandrillFallbackChunkMessage($campaign->getId(), 1, '<p>Bonjour *|FNAME|*</p>'));

        self::assertSame(MandrillFallbackChunkStatusEnum::Pending, $this->chunkRepository->findStatus($campaign->getId(), 1));
    }

    public function testEmptyEligibleRecipientsMarksSent(): void
    {
        $campaign = $this->createCampaign();
        // Member exists but is Refused (not eligible) -> chunk resolves to no recipient.
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Refused);
        $this->addChunkRow($campaign, 1, MandrillFallbackChunkStatusEnum::Pending);
        $this->manager->flush();

        ($this->handler)(new SendMandrillFallbackChunkMessage($campaign->getId(), 1, '<p>Bonjour *|FNAME|*</p>'));

        self::assertSame(MandrillFallbackChunkStatusEnum::Sent, $this->chunkRepository->findStatus($campaign->getId(), 1));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = self::getContainer()->get(SendMandrillFallbackChunkHandler::class);
        $this->chunkRepository = $this->getRepository(MandrillFallbackChunk::class);
    }

    protected function tearDown(): void
    {
        $this->handler = null;
        $this->chunkRepository = null;

        parent::tearDown();
    }

    private function createCampaign(): MailchimpCampaign
    {
        $message = new AdherentMessage();
        $message->setSubject('Sujet de campagne');
        $message->setContent('<p>Bonjour *|FNAME|*</p>');
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
        ?Adherent $adherent,
        int $chunkNumber,
        SegmentMemberStatusEnum $status,
    ): void {
        $member = new MailchimpStaticSegmentMember($campaign->getMailchimpStaticSegment(), $adherent, $chunkNumber);
        $member->processingStatus = $status;

        $this->manager->persist($member);
    }

    private function addChunkRow(MailchimpCampaign $campaign, int $chunkNumber, MandrillFallbackChunkStatusEnum $status): void
    {
        $chunk = new MandrillFallbackChunk($campaign, $chunkNumber);
        $chunk->status = $status;

        $this->manager->persist($chunk);
    }

    private function createSubscribedAdherent(): Adherent
    {
        $seq = ++$this->seq;
        $email = \sprintf('mandrill-chunk-%d@test.dev', $seq);

        $phone = new PhoneNumber();
        $phone->setCountryCode('FR');
        $phone->setNationalNumber('0140998211');

        $adherent = Adherent::create(
            Adherent::createUuid($email),
            \sprintf('MC-%d', $seq),
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
