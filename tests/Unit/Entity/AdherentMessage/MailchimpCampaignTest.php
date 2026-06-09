<?php

declare(strict_types=1);

namespace Tests\App\Unit\Entity\AdherentMessage;

use App\AdherentMessage\MailchimpStatusEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Audience\BlockReasonEnum;
use App\Mailchimp\Campaign\Audience\PreparationStatusEnum;
use App\Mailchimp\Campaign\RecoveryStatusEnum;
use PHPUnit\Framework\TestCase;

class MailchimpCampaignTest extends TestCase
{
    public function testInitialStateHasNotStartedPreparationStatus(): void
    {
        $campaign = $this->createCampaign();

        self::assertSame(PreparationStatusEnum::NotStarted, $campaign->getPreparationStatus());
        self::assertNull($campaign->getBlockReason());
        self::assertNull($campaign->getPreparedAt());
        self::assertNull($campaign->getPreparationLockedBy());
        self::assertFalse($campaign->isPendingSend());
    }

    public function testMarkAsPreparingResetsAllPreparationFieldsAndStoresLockedBy(): void
    {
        $alice = $this->createUser();
        $campaign = $this->createCampaign();
        // Simulates a previous state by triggering a ready cycle then re-preparation
        $campaign->markAsReady();

        $campaign->markAsPreparing($alice);

        self::assertSame(PreparationStatusEnum::Preparing, $campaign->getPreparationStatus());
        self::assertSame($alice, $campaign->getPreparationLockedBy());
        self::assertNull($campaign->getBlockReason());
        self::assertNull($campaign->getPreparedAt());
    }

    public function testMarkAsReadyAllowsSending(): void
    {
        $campaign = $this->createCampaign();

        $campaign->markAsReady();

        self::assertSame(PreparationStatusEnum::Ready, $campaign->getPreparationStatus());
        self::assertNotNull($campaign->getPreparedAt());
        self::assertTrue($campaign->canSend());
    }

    public function testCanSendIgnoresParentMessageSentStatus(): void
    {
        // Publication flow marks the parent AdherentMessage as SENT before the audience
        // preparation pipeline dispatches the actual Mailchimp send. canSend() must remain
        // true so FinalizeCampaignAudienceHandler can still trigger SendMailchimpCampaignCommand.
        $message = $this->createStub(AdherentMessage::class);
        $message->method('isSent')->willReturn(true);
        $campaign = $this->createCampaign($message);

        $campaign->markAsReady();

        self::assertTrue($campaign->canSend());
    }

    public function testCanSendWhenStillPreparingReturnsFalse(): void
    {
        $campaign = $this->createCampaign();
        $campaign->markAsPreparing($this->createUser());

        self::assertFalse($campaign->canSend());
    }

    public function testMarkAsFailedWithEmptyReasonReturnsFalseFromCanSend(): void
    {
        $campaign = $this->createCampaign();

        $campaign->markAsFailed(BlockReasonEnum::Empty);

        self::assertSame(PreparationStatusEnum::Failed, $campaign->getPreparationStatus());
        self::assertSame(BlockReasonEnum::Empty, $campaign->getBlockReason());
        self::assertFalse($campaign->canSend());
    }

    public function testMarkAsPendingSendFlipsFlag(): void
    {
        $campaign = $this->createCampaign();

        self::assertFalse($campaign->isPendingSend());

        $campaign->markAsPendingSend();

        self::assertTrue($campaign->isPendingSend());

        $campaign->clearPendingSend();

        self::assertFalse($campaign->isPendingSend());
    }

    public function testMarkAsFailedClearsPendingSend(): void
    {
        $campaign = $this->createCampaign();
        $campaign->markAsPendingSend();

        $campaign->markAsFailed(BlockReasonEnum::Empty);

        self::assertFalse($campaign->isPendingSend());
    }

    public function testRecoveryNotInProgressInitially(): void
    {
        $campaign = $this->createCampaign();

        self::assertFalse($campaign->isRecoveryInProgress());
        self::assertNull($campaign->getRecoveryStatus());
        self::assertNull($campaign->getRecoveryAttemptedAt());
        self::assertNull($campaign->getRecoveryOriginalExternalId());
    }

    public function testMarkRecoveryAttemptedSwapsToReplicaAndArchivesOriginal(): void
    {
        $campaign = $this->createCampaign();
        $campaign->setExternalId('mc-original');
        $campaign->status = MailchimpStatusEnum::Sent;

        $campaign->markRecoveryAttempted('mc-replica');

        self::assertSame('mc-replica', $campaign->getExternalId());
        self::assertSame('mc-original', $campaign->getRecoveryOriginalExternalId());
        self::assertSame(MailchimpStatusEnum::Save, $campaign->status, 'status reset so the send path does not skip it');
        self::assertSame(RecoveryStatusEnum::Attempted, $campaign->getRecoveryStatus());
        self::assertNotNull($campaign->getRecoveryAttemptedAt());
        self::assertTrue($campaign->isRecoveryInProgress());
    }

    public function testMarkRecoveryAbortedAfterSwapRestoresOriginalAndSentStatus(): void
    {
        $campaign = $this->createCampaign();
        $campaign->setExternalId('mc-original');
        $campaign->status = MailchimpStatusEnum::Sent;
        $campaign->markRecoveryAttempted('mc-replica');

        $campaign->markRecoveryAborted();

        self::assertSame('mc-original', $campaign->getExternalId(), 'restored so stats/reach track the campaign that delivered');
        self::assertSame(MailchimpStatusEnum::Sent, $campaign->status);
        self::assertSame(RecoveryStatusEnum::Aborted, $campaign->getRecoveryStatus());
        self::assertFalse($campaign->isRecoveryInProgress());
    }

    public function testMarkRecoveryAbortedBeforeSwapDoesNotTouchExternalId(): void
    {
        $campaign = $this->createCampaign();
        $campaign->setExternalId('mc-original');

        $campaign->markRecoveryAborted();

        self::assertSame('mc-original', $campaign->getExternalId());
        self::assertSame(RecoveryStatusEnum::Aborted, $campaign->getRecoveryStatus());
    }

    public function testMarkRecoverySucceededAndFailedSetStatus(): void
    {
        $campaign = $this->createCampaign();
        $campaign->setExternalId('mc-original');
        $campaign->markRecoveryAttempted('mc-replica');

        $campaign->markRecoverySucceeded();
        self::assertSame(RecoveryStatusEnum::Succeeded, $campaign->getRecoveryStatus());
        self::assertFalse($campaign->isRecoveryInProgress());

        $campaign->markRecoveryFailed();
        self::assertSame(RecoveryStatusEnum::Failed, $campaign->getRecoveryStatus());
    }

    private function createCampaign(?AdherentMessageInterface $message = null): MailchimpCampaign
    {
        return new MailchimpCampaign($message ?? $this->createStub(AdherentMessageInterface::class));
    }

    private function createUser(): Adherent
    {
        return $this->createStub(Adherent::class);
    }
}
