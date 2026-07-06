<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Hit\Stats;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Entity\AdherentMessage\MailchimpStaticSegmentMember;
use App\JeMengage\Hit\Stats\DTO\StatsOutput;
use App\JeMengage\Hit\Stats\Provider\PublicationProvider;
use App\JeMengage\Hit\TargetTypeEnum;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Membership\ActivityPositionsEnum;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Scope\ScopeEnum;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

/**
 * SES unsubscribes (carried on the segment-member rows) must surface in the publication stats sent to the
 * front, where they were structurally 0 (a SES send has no synced Mailchimp report). Counted per DISTINCT
 * adherent to stay consistent with the email-reach denominator.
 */
#[Group('functional')]
class PublicationUnsubscribeStatsTest extends AbstractKernelTestCase
{
    private int $seq = 0;

    protected function tearDown(): void
    {
        $this->seq = 0;

        parent::tearDown();
    }

    public function testCountUnsubscribedByMessageCountsDistinctAdherents(): void
    {
        $message = $this->createSentMessage();
        $segmentA = $this->addSegment($message);
        $segmentB = $this->addSegment($message);

        $sharedAdherent = $this->persistAdherent();
        // Same adherent across two segments of the message, both unsubscribed => must count once.
        $this->addMember($segmentA, $sharedAdherent, true);
        $this->addMember($segmentB, $sharedAdherent, true);
        // A second unsubscribed adherent.
        $this->addMember($segmentA, $this->persistAdherent(), true);
        // A member that did not unsubscribe must be excluded.
        $this->addMember($segmentA, $this->persistAdherent(), false);
        $this->manager->flush();

        self::assertSame(2, $this->repository()->countUnsubscribedByMessage((int) $message->getId()));
    }

    public function testPublicationProviderExposesSesUnsubscribes(): void
    {
        $message = $this->createSentMessage();
        $segment = $this->addSegment($message);
        $this->addMember($segment, $this->persistAdherent(), true);
        $this->addMember($segment, $this->persistAdherent(), true);
        $this->manager->flush();

        $stats = self::getContainer()->get(PublicationProvider::class)
            ->provide(TargetTypeEnum::Publication, $message->getUuid(), new StatsOutput());

        self::assertSame(2, $stats['unsubscribed']);
    }

    private function repository(): MailchimpStaticSegmentMemberRepository
    {
        return $this->getRepository(MailchimpStaticSegmentMember::class);
    }

    private function createSentMessage(): AdherentMessage
    {
        $message = new AdherentMessage(null, $this->persistAdherent());
        $message->setSubject('Lettre de campagne');
        $message->setContent('<p>Bonjour</p>');
        $message->setInstanceScope(ScopeEnum::NATIONAL);
        $message->markAsSent();

        $this->manager->persist($message);

        return $message;
    }

    private function addSegment(AdherentMessage $message): MailchimpStaticSegment
    {
        $campaign = new MailchimpCampaign($message);
        $message->addMailchimpCampaign($campaign);
        $segment = new MailchimpStaticSegment($campaign);
        $campaign->setMailchimpStaticSegment($segment);

        $this->manager->persist($campaign);
        $this->manager->persist($segment);

        return $segment;
    }

    private function addMember(MailchimpStaticSegment $segment, Adherent $adherent, bool $unsubscribed): void
    {
        $member = new MailchimpStaticSegmentMember($segment, $adherent, 1);
        $member->processingStatus = SegmentMemberStatusEnum::Sent;
        if ($unsubscribed) {
            $member->unsubscribedAt = new \DateTimeImmutable();
        }

        $this->manager->persist($member);
    }

    private function persistAdherent(): Adherent
    {
        $seq = ++$this->seq;
        $email = \sprintf('ses-stats-%d@test.dev', $seq);

        $phone = new PhoneNumber();
        $phone->setCountryCode(33);
        $phone->setNationalNumber('140998211');

        $adherent = Adherent::create(
            Adherent::createUuid($email),
            \sprintf('SES-S-%d', $seq),
            $email,
            'super-password',
            'female',
            'Sesstats',
            'Martin',
            new \DateTime('1990-12-12'),
            ActivityPositionsEnum::STUDENT,
            $this->createPostAddress('92 bld du Général Leclerc', '92110-92024'),
            $phone,
            status: Adherent::ENABLED,
        );

        $this->manager->persist($adherent);

        return $adherent;
    }
}
