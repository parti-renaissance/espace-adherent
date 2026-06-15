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
class MailchimpStaticSegmentMemberMandrillRecipientsTest extends AbstractKernelTestCase
{
    private ?MailchimpStaticSegmentMemberRepository $repository = null;
    private int $emailSeq = 0;

    public function testReturnsOnlyAddedSubscribedMembers(): void
    {
        $segment = $this->createSegment();
        $a1 = $this->createSubscribedAdherent();
        $a2 = $this->createSubscribedAdherent();
        $this->addMember($segment, $a1, 1, SegmentMemberStatusEnum::Added);
        $this->addMember($segment, $a2, 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        $recipients = $this->repository->findRecipientsForMandrillByChunk($segment->id, 1);

        self::assertCount(2, $recipients);
        $emails = array_column($recipients, 'email');
        self::assertContains($a1->getEmailAddress(), $emails);
        self::assertContains($a2->getEmailAddress(), $emails);
        // Merge fields needed by the Mandrill payload are present.
        self::assertArrayHasKey('firstName', $recipients[0]);
        self::assertArrayHasKey('lastName', $recipients[0]);
        self::assertArrayHasKey('gender', $recipients[0]);
        self::assertArrayHasKey('publicId', $recipients[0]);
    }

    public function testExcludesNonAddedStatuses(): void
    {
        $segment = $this->createSegment();
        $this->addMember($segment, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Pending);
        $this->addMember($segment, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Refused);
        $this->addMember($segment, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Errored);
        $this->manager->flush();

        self::assertCount(0, $this->repository->findRecipientsForMandrillByChunk($segment->id, 1));
    }

    public function testExcludesUnsubscribedOrCleanedAdherent(): void
    {
        $segment = $this->createSegment();

        $cleaned = $this->createSubscribedAdherent();
        $cleaned->clean();
        $unsubscribed = $this->createSubscribedAdherent();
        $unsubscribed->markAsUnsubscribe();

        $this->addMember($segment, $cleaned, 1, SegmentMemberStatusEnum::Added);
        $this->addMember($segment, $unsubscribed, 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        self::assertCount(0, $this->repository->findRecipientsForMandrillByChunk($segment->id, 1));
    }

    public function testExcludesNullAdherent(): void
    {
        $segment = $this->createSegment();
        $this->addMember($segment, null, 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        self::assertCount(0, $this->repository->findRecipientsForMandrillByChunk($segment->id, 1));
    }

    public function testFindIsScopedToChunk(): void
    {
        $segment = $this->createSegment();
        $this->addMember($segment, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->addMember($segment, $this->createSubscribedAdherent(), 2, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        self::assertCount(1, $this->repository->findRecipientsForMandrillByChunk($segment->id, 1));
        self::assertCount(1, $this->repository->findRecipientsForMandrillByChunk($segment->id, 2));
    }

    public function testCountEligibleCountsAcrossChunksIgnoringIneligible(): void
    {
        $segment = $this->createSegment();
        $this->addMember($segment, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->addMember($segment, $this->createSubscribedAdherent(), 2, SegmentMemberStatusEnum::Added);
        $this->addMember($segment, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Refused);
        $cleaned = $this->createSubscribedAdherent();
        $cleaned->clean();
        $this->addMember($segment, $cleaned, 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        self::assertSame(2, $this->repository->countEligibleForMandrill($segment->id));
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
        $email = \sprintf('mandrill-recipient-%d@test.dev', $seq);

        $phone = new PhoneNumber();
        $phone->setCountryCode('FR');
        $phone->setNationalNumber('0140998211');

        $adherent = Adherent::create(
            Adherent::createUuid($email),
            \sprintf('MR-%d', $seq),
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
    ): void {
        $member = new MailchimpStaticSegmentMember($segment, $adherent, $chunkNumber);
        $member->processingStatus = $status;

        $this->manager->persist($member);
    }
}
