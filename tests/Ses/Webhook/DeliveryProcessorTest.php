<?php

declare(strict_types=1);

namespace Tests\App\Ses\Webhook;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageReach;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Entity\AdherentMessage\MailchimpStaticSegmentMember;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Membership\ActivityPositionsEnum;
use App\Scope\ScopeEnum;
use App\Ses\Webhook\Processor\DeliveryDelayProcessor;
use App\Ses\Webhook\Processor\DeliveryProcessor;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Uid\Uuid;
use Tests\App\AbstractKernelTestCase;

/**
 * Real wiring of the delivery consumer: a SES DELIVERY event (SNS payload) sets deliveredAt on the
 * recipient's audience row, matched from the campaign_uuid/adherent_uuid tags through the actual repository
 * query (message -> campaign -> segment -> member). Exercises the DB join and the idempotent UPDATE, which
 * a mock-based unit test would not prove.
 */
#[Group('functional')]
class DeliveryProcessorTest extends AbstractKernelTestCase
{
    private int $seq = 0;

    protected function tearDown(): void
    {
        $this->seq = 0;

        parent::tearDown();
    }

    public function testDeliveryEventMarksRecipientRowDelivered(): void
    {
        [$message, $recipient, $segmentId] = $this->createSentCampaignWithRecipient();

        $this->handle($message->getUuid(), $recipient->getUuid(), '2026-07-02T14:35:10.000Z');

        $member = $this->reloadMember($segmentId, $recipient->getUuid());
        self::assertNotNull($member->deliveredAt);
        self::assertSame('2026-07-02 14:35:10', $member->deliveredAt->format('Y-m-d H:i:s'));
    }

    public function testDuplicateDeliveryKeepsTheFirstTimestamp(): void
    {
        [$message, $recipient, $segmentId] = $this->createSentCampaignWithRecipient();

        $this->handle($message->getUuid(), $recipient->getUuid(), '2026-07-02T14:35:10.000Z');
        $this->handle($message->getUuid(), $recipient->getUuid(), '2026-07-02T18:00:00.000Z');

        $member = $this->reloadMember($segmentId, $recipient->getUuid());
        self::assertSame('2026-07-02 14:35:10', $member->deliveredAt->format('Y-m-d H:i:s'), 'A redelivered event must not overwrite the first delivery timestamp.');
    }

    public function testDeliveryForAdherentNotInAudienceIsANoop(): void
    {
        [$message, $recipient, $segmentId] = $this->createSentCampaignWithRecipient();
        $stranger = $this->persistAdherent();
        $this->manager->flush();

        $this->handle($message->getUuid(), $stranger->getUuid(), '2026-07-02T14:35:10.000Z');

        $member = $this->reloadMember($segmentId, $recipient->getUuid());
        self::assertNull($member->deliveredAt, 'A delivery for an adherent outside the audience must not touch other rows.');
    }

    public function testDeliveryDelayEventRecordsDelayedAtAndType(): void
    {
        [$message, $recipient, $segmentId] = $this->createSentCampaignWithRecipient();

        $this->handleDelay($message->getUuid(), $recipient->getUuid(), '2026-07-02T15:10:00.000Z', 'Throttling');

        $member = $this->reloadMember($segmentId, $recipient->getUuid());
        self::assertSame('2026-07-02 15:10:00', $member->delayedAt->format('Y-m-d H:i:s'));
        self::assertSame('Throttling', $member->delayType);
        self::assertNull($member->deliveredAt, 'A delay does not confirm delivery.');
    }

    public function testLatestDelayOverwritesThePreviousOne(): void
    {
        [$message, $recipient, $segmentId] = $this->createSentCampaignWithRecipient();

        $this->handleDelay($message->getUuid(), $recipient->getUuid(), '2026-07-02T15:10:00.000Z', 'Throttling');
        $this->handleDelay($message->getUuid(), $recipient->getUuid(), '2026-07-02T17:45:00.000Z', 'MailboxFull');

        $member = $this->reloadMember($segmentId, $recipient->getUuid());
        self::assertSame('2026-07-02 17:45:00', $member->delayedAt->format('Y-m-d H:i:s'), 'The last delay wins.');
        self::assertSame('MailboxFull', $member->delayType);
    }

    public function testDeliveryPromotesAQuarantinedRowAndRecordsItsReach(): void
    {
        // The send failed ambiguously (5xx/network): the row was quarantined because SES may or may not have
        // accepted it. This delivery event is the proof that it did — and it must promote the row on the spot,
        // not wait for the timed reconciliation, whose delayed message can be lost with the broker.
        [$message, $recipient, $segmentId] = $this->createSentCampaignWithRecipient(SegmentMemberStatusEnum::SendErrored);

        $this->handle($message->getUuid(), $recipient->getUuid(), '2026-07-02T14:35:10.000Z');

        $member = $this->reloadMember($segmentId, $recipient->getUuid());
        self::assertSame(SegmentMemberStatusEnum::Sent, $member->processingStatus, 'the event proves the ambiguous send did happen');
        self::assertNotNull($member->deliveredAt);
        self::assertSame(1, $this->countReach($message->getId()), 'the promoted recipient joins the campaign reach');
    }

    public function testDeliveryLeavesANonQuarantinedRowStatusAlone(): void
    {
        // Only a quarantined row is ever promoted: an event on a row already closed must not rewrite its status
        // (a Refused row stays Refused — SES never accepted it in the first place).
        [$message, $recipient, $segmentId] = $this->createSentCampaignWithRecipient(SegmentMemberStatusEnum::Refused);

        $this->handle($message->getUuid(), $recipient->getUuid(), '2026-07-02T14:35:10.000Z');

        $member = $this->reloadMember($segmentId, $recipient->getUuid());
        self::assertSame(SegmentMemberStatusEnum::Refused, $member->processingStatus);
        self::assertSame(0, $this->countReach($message->getId()));
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

    private function handle(Uuid $messageUuid, Uuid $adherentUuid, string $timestamp): void
    {
        $payload = ['Message' => json_encode([
            'eventType' => 'Delivery',
            'mail' => ['tags' => [
                'campaign_uuid' => [$messageUuid->toRfc4122()],
                'adherent_uuid' => [$adherentUuid->toRfc4122()],
            ]],
            'delivery' => ['timestamp' => $timestamp],
        ])];

        self::getContainer()->get(DeliveryProcessor::class)->process($payload);
        $this->manager->clear();
    }

    private function handleDelay(Uuid $messageUuid, Uuid $adherentUuid, string $timestamp, string $delayType): void
    {
        $payload = ['Message' => json_encode([
            'eventType' => 'DeliveryDelay',
            'mail' => ['tags' => [
                'campaign_uuid' => [$messageUuid->toRfc4122()],
                'adherent_uuid' => [$adherentUuid->toRfc4122()],
            ]],
            'deliveryDelay' => ['timestamp' => $timestamp, 'delayType' => $delayType],
        ])];

        self::getContainer()->get(DeliveryDelayProcessor::class)->process($payload);
        $this->manager->clear();
    }

    /**
     * @return array{AdherentMessage, Adherent, int}
     */
    private function createSentCampaignWithRecipient(SegmentMemberStatusEnum $status = SegmentMemberStatusEnum::Sent): array
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
        $member->processingStatus = $status;
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
        $email = \sprintf('ses-delivery-%d@test.dev', $seq);

        $phone = new PhoneNumber();
        $phone->setCountryCode(33);
        $phone->setNationalNumber('140998211');

        $adherent = Adherent::create(
            Adherent::createUuid($email),
            \sprintf('SES-D-%d', $seq),
            $email,
            'super-password',
            'female',
            'Sesdelivery',
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
