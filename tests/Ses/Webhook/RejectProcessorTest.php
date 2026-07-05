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
use App\Ses\Webhook\Processor\RejectProcessor;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Uid\Uuid;
use Tests\App\AbstractKernelTestCase;

/**
 * Real wiring of the reject consumer: a SES REJECT event (SNS payload) sets rejectedAt + rejectReason on the
 * recipient's audience row, matched from the campaign_uuid/adherent_uuid tags through the actual repository
 * query. No global action is taken (reject = content problem, not an address problem).
 */
#[Group('functional')]
class RejectProcessorTest extends AbstractKernelTestCase
{
    private int $seq = 0;

    protected function tearDown(): void
    {
        $this->seq = 0;

        parent::tearDown();
    }

    public function testRejectEventMarksRecipientRowRejected(): void
    {
        [$message, $recipient, $segmentId] = $this->createSentCampaignWithRecipient();

        $this->handle($message->getUuid(), $recipient->getUuid(), '2026-07-02T14:35:10.000Z', 'Bad content');

        $member = $this->reloadMember($segmentId, $recipient->getUuid());
        self::assertNotNull($member->rejectedAt);
        self::assertSame('2026-07-02 14:35:10', $member->rejectedAt->format('Y-m-d H:i:s'));
        self::assertSame('Bad content', $member->rejectReason);
    }

    public function testDuplicateRejectKeepsTheFirstTimestamp(): void
    {
        [$message, $recipient, $segmentId] = $this->createSentCampaignWithRecipient();

        $this->handle($message->getUuid(), $recipient->getUuid(), '2026-07-02T14:35:10.000Z', 'Bad content');
        $this->handle($message->getUuid(), $recipient->getUuid(), '2026-07-02T18:00:00.000Z', 'Other reason');

        $member = $this->reloadMember($segmentId, $recipient->getUuid());
        self::assertSame('2026-07-02 14:35:10', $member->rejectedAt->format('Y-m-d H:i:s'), 'A replayed reject must not overwrite the first timestamp.');
        self::assertSame('Bad content', $member->rejectReason);
    }

    public function testRejectForAdherentNotInAudienceIsANoop(): void
    {
        [$message, $recipient, $segmentId] = $this->createSentCampaignWithRecipient();
        $stranger = $this->persistAdherent();
        $this->manager->flush();

        $this->handle($message->getUuid(), $stranger->getUuid(), '2026-07-02T14:35:10.000Z', 'Bad content');

        $member = $this->reloadMember($segmentId, $recipient->getUuid());
        self::assertNull($member->rejectedAt, 'A reject for an adherent outside the audience must not touch other rows.');
    }

    public function testRejectWithoutTagsIsANoop(): void
    {
        [$message, $recipient, $segmentId] = $this->createSentCampaignWithRecipient();

        // Direct-identity style payload: no mail.tags => cannot attribute to a member.
        $payload = ['Message' => json_encode([
            'eventType' => 'Reject',
            'mail' => ['timestamp' => '2026-07-02T14:35:10.000Z'],
            'reject' => ['reason' => 'Bad content'],
        ])];
        self::getContainer()->get(RejectProcessor::class)->process($payload);
        $this->manager->clear();

        $member = $this->reloadMember($segmentId, $recipient->getUuid());
        self::assertNull($member->rejectedAt);
    }

    private function handle(Uuid $messageUuid, Uuid $adherentUuid, string $timestamp, string $reason): void
    {
        $payload = ['Message' => json_encode([
            'eventType' => 'Reject',
            'mail' => [
                'tags' => [
                    'campaign_uuid' => [$messageUuid->toRfc4122()],
                    'adherent_uuid' => [$adherentUuid->toRfc4122()],
                ],
                'timestamp' => $timestamp,
            ],
            'reject' => ['reason' => $reason],
        ])];

        self::getContainer()->get(RejectProcessor::class)->process($payload);
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
        $email = \sprintf('ses-reject-%d@test.dev', $seq);

        $phone = new PhoneNumber();
        $phone->setCountryCode(33);
        $phone->setNationalNumber('140998211');

        $adherent = Adherent::create(
            Adherent::createUuid($email),
            \sprintf('SES-J-%d', $seq),
            $email,
            'super-password',
            'female',
            'Sesreject',
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
