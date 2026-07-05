<?php

declare(strict_types=1);

namespace Tests\App\Ses\Webhook;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Entity\AdherentMessage\MailchimpStaticSegmentMember;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Membership\ActivityPositionsEnum;
use App\Scope\ScopeEnum;
use App\Ses\Webhook\Processor\FeedbackAttributionProcessor;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Uid\Uuid;
use Tests\App\AbstractKernelTestCase;

/**
 * Real wiring of the per-member feedback attribution: a config-set Bounce (Permanent) / Complaint sets
 * bouncedAt+bounceSubType / complainedAt on the recipient's audience row, matched from the tags. This is the
 * per-campaign layer that runs IN ADDITION to the untouched global email-keyed suppression.
 */
#[Group('functional')]
class FeedbackAttributionProcessorTest extends AbstractKernelTestCase
{
    private int $seq = 0;

    protected function tearDown(): void
    {
        $this->seq = 0;

        parent::tearDown();
    }

    public function testPermanentBounceMarksRecipientRowBounced(): void
    {
        [$message, $recipient, $segmentId] = $this->createSentCampaignWithRecipient();

        $this->handleBounce($message->getUuid(), $recipient->getUuid(), '2026-07-02T14:35:10.000Z', 'General');

        $member = $this->reloadMember($segmentId, $recipient->getUuid());
        self::assertNotNull($member->bouncedAt);
        self::assertSame('2026-07-02 14:35:10', $member->bouncedAt->format('Y-m-d H:i:s'));
        self::assertSame('General', $member->bounceSubType);
        self::assertNull($member->complainedAt);
    }

    public function testComplaintMarksRecipientRowComplained(): void
    {
        [$message, $recipient, $segmentId] = $this->createSentCampaignWithRecipient();

        $this->handleComplaint($message->getUuid(), $recipient->getUuid(), '2026-07-02T16:00:00.000Z');

        $member = $this->reloadMember($segmentId, $recipient->getUuid());
        self::assertNotNull($member->complainedAt);
        self::assertSame('2026-07-02 16:00:00', $member->complainedAt->format('Y-m-d H:i:s'));
        self::assertNull($member->bouncedAt);
    }

    public function testDuplicateBounceKeepsTheFirstTimestamp(): void
    {
        [$message, $recipient, $segmentId] = $this->createSentCampaignWithRecipient();

        $this->handleBounce($message->getUuid(), $recipient->getUuid(), '2026-07-02T14:35:10.000Z', 'General');
        $this->handleBounce($message->getUuid(), $recipient->getUuid(), '2026-07-02T18:00:00.000Z', 'NoEmail');

        $member = $this->reloadMember($segmentId, $recipient->getUuid());
        self::assertSame('2026-07-02 14:35:10', $member->bouncedAt->format('Y-m-d H:i:s'), 'A replayed bounce must not overwrite the first timestamp.');
        self::assertSame('General', $member->bounceSubType);
    }

    public function testBounceForAdherentNotInAudienceIsANoop(): void
    {
        [$message, $recipient, $segmentId] = $this->createSentCampaignWithRecipient();
        $stranger = $this->persistAdherent();
        $this->manager->flush();

        $this->handleBounce($message->getUuid(), $stranger->getUuid(), '2026-07-02T14:35:10.000Z', 'General');

        $member = $this->reloadMember($segmentId, $recipient->getUuid());
        self::assertNull($member->bouncedAt, 'A bounce for an adherent outside the audience must not touch other rows.');
    }

    public function testBounceWithoutTagsIsANoop(): void
    {
        [$message, $recipient, $segmentId] = $this->createSentCampaignWithRecipient();

        // Direct-identity style payload: no eventType, no mail.tags => the global handler alone acts,
        // per-member attribution degrades to a no-op.
        $payload = ['Message' => json_encode([
            'notificationType' => 'Bounce',
            'mail' => [],
            'bounce' => ['bounceType' => 'Permanent', 'timestamp' => '2026-07-02T14:35:10.000Z'],
        ])];
        self::getContainer()->get(FeedbackAttributionProcessor::class)->process($payload);
        $this->manager->clear();

        $member = $this->reloadMember($segmentId, $recipient->getUuid());
        self::assertNull($member->bouncedAt);
    }

    private function handleBounce(Uuid $messageUuid, Uuid $adherentUuid, string $timestamp, string $subType): void
    {
        $payload = ['Message' => json_encode([
            'eventType' => 'Bounce',
            'mail' => ['tags' => [
                'campaign_uuid' => [$messageUuid->toRfc4122()],
                'adherent_uuid' => [$adherentUuid->toRfc4122()],
            ]],
            'bounce' => ['bounceType' => 'Permanent', 'bounceSubType' => $subType, 'timestamp' => $timestamp],
        ])];

        self::getContainer()->get(FeedbackAttributionProcessor::class)->process($payload);
        $this->manager->clear();
    }

    private function handleComplaint(Uuid $messageUuid, Uuid $adherentUuid, string $timestamp): void
    {
        $payload = ['Message' => json_encode([
            'eventType' => 'Complaint',
            'mail' => ['tags' => [
                'campaign_uuid' => [$messageUuid->toRfc4122()],
                'adherent_uuid' => [$adherentUuid->toRfc4122()],
            ]],
            'complaint' => ['timestamp' => $timestamp],
        ])];

        self::getContainer()->get(FeedbackAttributionProcessor::class)->process($payload);
        $this->manager->clear();
    }

    /**
     * @return array{AdherentMessage, Adherent, int}
     */
    private function createSentCampaignWithRecipient(): array
    {
        $author = $this->persistAdherent();
        $recipient = $this->persistAdherent();

        $message = new AdherentMessage(null, $author);
        $message->setSubject('Lettre de campagne');
        $message->setContent('<p>Bonjour {{Prénom}}</p>');
        $message->setInstanceScope(ScopeEnum::NATIONAL);
        $message->markAsSent();

        $campaign = new MailchimpCampaign($message);
        $message->addMailchimpCampaign($campaign);
        $segment = new MailchimpStaticSegment($campaign);
        $campaign->setMailchimpStaticSegment($segment);

        $member = new MailchimpStaticSegmentMember($segment, $recipient, 1);
        $member->processingStatus = SegmentMemberStatusEnum::Sent;
        $member->processedAt = new \DateTimeImmutable('2026-07-02 14:33:30');

        $this->manager->persist($message);
        $this->manager->persist($campaign);
        $this->manager->persist($segment);
        $this->manager->persist($member);
        $this->manager->flush();

        return [$message, $recipient, $segment->id];
    }

    private function persistAdherent(): Adherent
    {
        $seq = ++$this->seq;
        $email = \sprintf('ses-feedback-%d@test.dev', $seq);

        $phone = new PhoneNumber();
        $phone->setCountryCode(33);
        $phone->setNationalNumber('140998211');

        $adherent = Adherent::create(
            Adherent::createUuid($email),
            \sprintf('SES-F-%d', $seq),
            $email,
            'super-password',
            'female',
            'Sesfeedback',
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

    private function reloadMember(int $segmentId, Uuid $adherentUuid): MailchimpStaticSegmentMember
    {
        $member = $this->getRepository(MailchimpStaticSegmentMember::class)
            ->createQueryBuilder('m')
            ->innerJoin('m.adherent', 'a')
            ->where('IDENTITY(m.staticSegment) = :sid')
            ->andWhere('a.uuid = :uuid')
            ->setParameter('sid', $segmentId)
            ->setParameter('uuid', $adherentUuid->toRfc4122())
            ->getQuery()
            ->getOneOrNullResult()
        ;

        self::assertInstanceOf(MailchimpStaticSegmentMember::class, $member);

        return $member;
    }
}
