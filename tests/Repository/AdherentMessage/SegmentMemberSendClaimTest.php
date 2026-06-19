<?php

declare(strict_types=1);

namespace Tests\App\Repository\AdherentMessage;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Entity\AdherentMessage\MailchimpStaticSegmentMember;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Membership\ActivityPositionsEnum;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class SegmentMemberSendClaimTest extends AbstractKernelTestCase
{
    private ?MailchimpStaticSegmentMemberRepository $repository = null;
    private int $emailSeq = 0;

    public function testClaimRowForSendingSucceedsOnceThenFails(): void
    {
        $segment = $this->createSegment();
        $member = $this->addMember($segment, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        self::assertTrue($this->repository->claimRowForSending($member->id), 'first claim wins');
        self::assertFalse($this->repository->claimRowForSending($member->id), 'second claim loses (already Sending)');

        $this->manager->refresh($member);
        self::assertSame(SegmentMemberStatusEnum::Sending, $member->processingStatus);
    }

    public function testMarkRowSentTransitionsFromSending(): void
    {
        $segment = $this->createSegment();
        $member = $this->addMember($segment, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        $this->repository->claimRowForSending($member->id);
        $this->repository->markRowSent($member->id);

        $this->manager->refresh($member);
        self::assertSame(SegmentMemberStatusEnum::Sent, $member->processingStatus);
        self::assertNotNull($member->processedAt);
    }

    public function testReopenRowReturnsClaimedRowToAdded(): void
    {
        $segment = $this->createSegment();
        $member = $this->addMember($segment, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        $this->repository->claimRowForSending($member->id);
        $this->repository->reopenRow($member->id);

        $this->manager->refresh($member);
        self::assertSame(SegmentMemberStatusEnum::Added, $member->processingStatus);
        // Reopened row is claimable again (clean retry, no duplicate since the send never happened).
        self::assertTrue($this->repository->claimRowForSending($member->id));
    }

    public function testFindClaimableRecipientsByChunkReturnsAddedSubscribedWithId(): void
    {
        $segment = $this->createSegment();
        $added = $this->addMember($segment, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        // Excluded: a row already Sent, and an unsubscribed adherent.
        $this->addMember($segment, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Sent);
        $unsubscribed = $this->createSubscribedAdherent();
        $unsubscribed->markAsUnsubscribe();
        $this->addMember($segment, $unsubscribed, 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        $rows = $this->repository->findClaimableRecipientsByChunk($segment->id, 1);

        self::assertCount(1, $rows);
        self::assertSame($added->id, $rows[0]['id']);
        self::assertArrayHasKey('email', $rows[0]);
        self::assertArrayHasKey('firstName', $rows[0]);
        self::assertArrayHasKey('gender', $rows[0]);
        self::assertArrayHasKey('publicId', $rows[0]);
    }

    public function testCountRemainingToSendCountsSendingAndAddedSubscribedOnly(): void
    {
        $segment = $this->createSegment();
        // Counted: 1 Added subscribed + 1 Sending.
        $this->addMember($segment, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $sending = $this->addMember($segment, $this->createSubscribedAdherent(), 2, SegmentMemberStatusEnum::Added);
        // Not counted: Sent, and Added-but-unsubscribed (never sendable -> must not block completion).
        $this->addMember($segment, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Sent);
        $unsubscribed = $this->createSubscribedAdherent();
        $unsubscribed->markAsUnsubscribe();
        $this->addMember($segment, $unsubscribed, 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        $this->repository->claimRowForSending($sending->id);

        self::assertSame(2, $this->repository->countRemainingToSend($segment->id));
    }

    public function testFindChunkNumbersToSendListsChunksWithAddedSubscribed(): void
    {
        $segment = $this->createSegment();
        $this->addMember($segment, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->addMember($segment, $this->createSubscribedAdherent(), 3, SegmentMemberStatusEnum::Added);
        // Chunk 2 has only a Sent row -> no work -> excluded.
        $this->addMember($segment, $this->createSubscribedAdherent(), 2, SegmentMemberStatusEnum::Sent);
        $this->manager->flush();

        self::assertSame([1, 3], $this->repository->findChunkNumbersToSend($segment->id));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->getRepository(MailchimpStaticSegmentMember::class);
    }

    protected function tearDown(): void
    {
        $this->repository = null;

        parent::tearDown();
    }

    private function createSegment(): MailchimpStaticSegment
    {
        $message = new AdherentMessage();
        $message->setContent('<p>Bonjour *|FNAME|*</p>');
        $campaign = new MailchimpCampaign($message);
        $segment = new MailchimpStaticSegment($campaign);
        $campaign->setMailchimpStaticSegment($segment);

        $this->manager->persist($message);
        $this->manager->persist($campaign);
        $this->manager->persist($segment);
        $this->manager->flush();

        return $segment;
    }

    private function createSubscribedAdherent(): Adherent
    {
        $seq = ++$this->emailSeq;
        $email = \sprintf('ses-claim-%d@test.dev', $seq);

        $phone = new PhoneNumber();
        $phone->setCountryCode('FR');
        $phone->setNationalNumber('0140998211');

        $adherent = Adherent::create(
            Adherent::createUuid($email),
            \sprintf('SC-%d', $seq),
            $email,
            'super-password',
            'male',
            'John',
            'Smith',
            new \DateTime('1990-12-12'),
            ActivityPositionsEnum::STUDENT,
            $this->createPostAddress('92 bld du Général Leclerc', '92110-92024'),
            $phone
        );

        $this->manager->persist($adherent);

        return $adherent;
    }

    private function addMember(
        MailchimpStaticSegment $segment,
        ?Adherent $adherent,
        int $chunkNumber,
        SegmentMemberStatusEnum $status,
    ): MailchimpStaticSegmentMember {
        $member = new MailchimpStaticSegmentMember($segment, $adherent, $chunkNumber);
        $member->processingStatus = $status;

        $this->manager->persist($member);

        return $member;
    }
}
