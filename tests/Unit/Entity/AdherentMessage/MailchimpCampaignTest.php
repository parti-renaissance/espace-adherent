<?php

declare(strict_types=1);

namespace Tests\App\Unit\Entity\AdherentMessage;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Audience\AudienceCheckEnum;
use App\Mailchimp\Campaign\Audience\BlockReasonEnum;
use App\Mailchimp\Campaign\Audience\PreparationStatusEnum;
use PHPUnit\Framework\TestCase;

class MailchimpCampaignTest extends TestCase
{
    public function testInitialStateHasNotStartedPreparationStatus(): void
    {
        $campaign = $this->createCampaign();

        self::assertSame(PreparationStatusEnum::NotStarted, $campaign->getPreparationStatus());
        self::assertNull($campaign->getAudienceCheck());
        self::assertNull($campaign->getBlockReason());
        self::assertNull($campaign->getPreparedAt());
        self::assertNull($campaign->getPreparationLockedBy());
        self::assertFalse($campaign->isCancellationRequested());
    }

    public function testMarkAsPreparingResetsAllPreparationFieldsAndStoresLockedBy(): void
    {
        $campaign = $this->createCampaign();
        // Simulates a previous state by triggering a ready cycle then re-preparation
        $campaign->markAsReady(AudienceCheckEnum::Drift);

        $campaign->markAsPreparing('alice@example.com');

        self::assertSame(PreparationStatusEnum::Preparing, $campaign->getPreparationStatus());
        self::assertSame('alice@example.com', $campaign->getPreparationLockedBy());
        self::assertNull($campaign->getBlockReason());
        self::assertNull($campaign->getAudienceCheck());
        self::assertNull($campaign->getPreparedAt());
        self::assertFalse($campaign->isCancellationRequested());
    }

    public function testMarkAsReadyWithMatchAudienceAllowsSendingWhenMessageNotSent(): void
    {
        $campaign = $this->createCampaign($this->createStub(AdherentMessage::class));

        $campaign->markAsReady(AudienceCheckEnum::Match);

        self::assertSame(PreparationStatusEnum::Ready, $campaign->getPreparationStatus());
        self::assertSame(AudienceCheckEnum::Match, $campaign->getAudienceCheck());
        self::assertNotNull($campaign->getPreparedAt());
        self::assertTrue($campaign->canSend());
    }

    public function testCanSendWithDriftAudienceReturnsTrue(): void
    {
        $message = $this->createStub(AdherentMessage::class);
        $message->method('isSent')->willReturn(false);
        $campaign = $this->createCampaign($message);

        $campaign->markAsReady(AudienceCheckEnum::Drift);

        self::assertTrue($campaign->canSend());
    }

    public function testCanSendWithMismatchAudienceReturnsFalse(): void
    {
        $message = $this->createStub(AdherentMessage::class);
        $message->method('isSent')->willReturn(false);
        $campaign = $this->createCampaign($message);

        $campaign->markAsReady(AudienceCheckEnum::Mismatch);

        self::assertFalse($campaign->canSend());
    }

    public function testCanSendWhenMessageAlreadySentReturnsFalse(): void
    {
        $message = $this->createStub(AdherentMessage::class);
        $message->method('isSent')->willReturn(true);
        $campaign = $this->createCampaign($message);

        $campaign->markAsReady(AudienceCheckEnum::Match);

        self::assertFalse($campaign->canSend());
    }

    public function testCanSendWhenStillPreparingReturnsFalse(): void
    {
        $campaign = $this->createCampaign();
        $campaign->markAsPreparing('user@example.com');

        self::assertFalse($campaign->canSend());
    }

    public function testMarkAsFailedWithEmptyReasonReturnsFalseFromCanSend(): void
    {
        $campaign = $this->createCampaign();

        $campaign->markAsFailed(BlockReasonEnum::Empty, 'Audience SQL retourne 0 emails valides.');

        self::assertSame(PreparationStatusEnum::Failed, $campaign->getPreparationStatus());
        self::assertSame(BlockReasonEnum::Empty, $campaign->getBlockReason());
        self::assertSame('Audience SQL retourne 0 emails valides.', $campaign->getPreparationFailureDetail());
        self::assertFalse($campaign->canSend());
    }

    public function testRequestCancellationFlipsTheFlag(): void
    {
        $campaign = $this->createCampaign();

        self::assertFalse($campaign->isCancellationRequested());

        $campaign->requestCancellation();

        self::assertTrue($campaign->isCancellationRequested());
    }

    private function createCampaign(?AdherentMessageInterface $message = null): MailchimpCampaign
    {
        return new MailchimpCampaign($message ?? $this->createStub(AdherentMessageInterface::class));
    }
}
