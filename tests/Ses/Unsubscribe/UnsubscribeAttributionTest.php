<?php

declare(strict_types=1);

namespace Tests\App\Ses\Unsubscribe;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Entity\AdherentMessage\MailchimpStaticSegmentMember;
use App\History\Command\UserActionHistoryCommand;
use App\History\UserActionHistoryTypeEnum;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Mailchimp\Contact\ContactStatusEnum;
use App\Membership\ActivityPositionsEnum;
use App\Messenger\MessageRecorder\MessageRecorderInterface;
use App\Scope\ScopeEnum;
use App\Ses\Unsubscribe\UnsubscribeManager;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;
use Tests\App\MessengerTestTrait;

/**
 * Real wiring of the per-send unsubscribe attribution: a click on the unsubscribe link carries the exact
 * sent row (member_id) and the durable send identity (message_uuid). unsubscribe() sets unsubscribedAt on THAT
 * row only (funnel) and dispatches a durable UserActionHistory audit — both on top of the global opt-out.
 */
#[Group('functional')]
class UnsubscribeAttributionTest extends AbstractKernelTestCase
{
    use MessengerTestTrait;

    private int $seq = 0;

    protected function tearDown(): void
    {
        $this->seq = 0;

        parent::tearDown();
    }

    public function testUnsubscribeMarksExactMemberRow(): void
    {
        [$recipient, $memberId] = $this->createSentCampaignWithRecipient();

        $this->unsubscribeManager()->unsubscribe($recipient, $memberId, null);
        $this->manager->clear();

        self::assertNotNull($this->reloadMember($memberId)->unsubscribedAt);
    }

    public function testUnsubscribeWithoutMemberIdMarksNothing(): void
    {
        [$recipient, $memberId] = $this->createSentCampaignWithRecipient();

        $this->unsubscribeManager()->unsubscribe($recipient, null, null);
        $this->manager->clear();

        self::assertNull($this->reloadMember($memberId)->unsubscribedAt);
    }

    public function testUnsubscribeIsIdempotentOnMemberRow(): void
    {
        [$recipient, $memberId] = $this->createSentCampaignWithRecipient();

        $this->unsubscribeManager()->unsubscribe($recipient, $memberId, null);
        $this->manager->clear();
        $firstTimestamp = $this->reloadMember($memberId)->unsubscribedAt;

        // A second POST (redelivery / scanner) must not rewrite the first timestamp (guarded on IS NULL).
        // The global guard also short-circuits once the adherent is already unsubscribed.
        $recipient = $this->getRepository(Adherent::class)->find($recipient->getId());
        $this->unsubscribeManager()->unsubscribe($recipient, $memberId, null);
        $this->manager->clear();

        self::assertEquals($firstTimestamp, $this->reloadMember($memberId)->unsubscribedAt);
    }

    public function testUnsubscribeWithPurgedMemberIdIsANoopButOptOutHappens(): void
    {
        [$recipient, $memberId] = $this->createSentCampaignWithRecipient();

        // member_id points to a row that no longer exists (audience re-prepared): attribution is a no-op,
        // the global opt-out still happens.
        $this->unsubscribeManager()->unsubscribe($recipient, $memberId + 100_000, null);
        $this->manager->clear();

        self::assertNull($this->reloadMember($memberId)->unsubscribedAt);
        $reloaded = $this->getRepository(Adherent::class)->find($recipient->getId());
        self::assertSame(ContactStatusEnum::UNSUBSCRIBED, $reloaded->getMailchimpStatus());
    }

    public function testUnsubscribeDispatchesDurableAuditWithMessageUuid(): void
    {
        [$recipient, $memberId, $messageUuid] = $this->createSentCampaignWithRecipient();

        $this->unsubscribeManager()->unsubscribe($recipient, $memberId, $messageUuid);

        $command = $this->dispatchedUserActionHistoryCommand();
        self::assertNotNull($command, 'A durable unsubscribe audit must be dispatched.');
        self::assertSame(UserActionHistoryTypeEnum::EMAIL_UNSUBSCRIBE, $command->type);
        // The audit snapshots the send so it stays readable even if the message is later deleted.
        self::assertSame($messageUuid, $command->data['message_uuid'] ?? null);
        self::assertSame('Lettre de campagne', $command->data['subject'] ?? null);
        self::assertSame(ScopeEnum::NATIONAL, $command->data['instance'] ?? null);
        self::assertArrayHasKey('sender_name', $command->data);
    }

    public function testUnsubscribeWithoutMessageUuidDispatchesNoAudit(): void
    {
        [$recipient, $memberId] = $this->createSentCampaignWithRecipient();

        $this->unsubscribeManager()->unsubscribe($recipient, $memberId, null);

        self::assertNull($this->dispatchedUserActionHistoryCommand());
    }

    private function dispatchedUserActionHistoryCommand(): ?UserActionHistoryCommand
    {
        foreach ($this->getMessageRecorder()->getMessages() as $envelope) {
            $message = $envelope->getMessage();
            if ($message instanceof UserActionHistoryCommand) {
                return $message;
            }
        }

        return null;
    }

    protected function getMessageRecorder(): MessageRecorderInterface
    {
        return self::getContainer()->get(MessageRecorderInterface::class);
    }

    private function unsubscribeManager(): UnsubscribeManager
    {
        return self::getContainer()->get(UnsubscribeManager::class);
    }

    /**
     * @return array{Adherent, int, string}
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

        return [$recipient, (int) $member->id, $message->getUuid()->toRfc4122()];
    }

    private function persistAdherent(): Adherent
    {
        $seq = ++$this->seq;
        $email = \sprintf('ses-unsub-%d@test.dev', $seq);

        $phone = new PhoneNumber();
        $phone->setCountryCode(33);
        $phone->setNationalNumber('140998211');

        $adherent = Adherent::create(
            Adherent::createUuid($email),
            \sprintf('SES-U-%d', $seq),
            $email,
            'super-password',
            'female',
            'Sesunsub',
            'Martin',
            new \DateTime('1990-12-12'),
            ActivityPositionsEnum::STUDENT,
            $this->createPostAddress('92 bld du Général Leclerc', '92110-92024'),
            $phone,
            status: Adherent::ENABLED,
        );

        $this->manager->persist($adherent);
        $this->manager->flush();

        return $adherent;
    }

    private function reloadMember(int $memberId): MailchimpStaticSegmentMember
    {
        $member = $this->getRepository(MailchimpStaticSegmentMember::class)->find($memberId);

        self::assertInstanceOf(MailchimpStaticSegmentMember::class, $member);

        return $member;
    }
}
