<?php

declare(strict_types=1);

namespace Tests\App\Unit\Entity\AdherentMessage;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Audience\BlockReasonEnum;
use App\Mailchimp\Campaign\Audience\PreparationStatusEnum;
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

    public function testMarkAsReadyAllowsSendingWhenMessageNotSent(): void
    {
        $message = $this->createStub(AdherentMessage::class);
        $message->method('isSent')->willReturn(false);
        $campaign = $this->createCampaign($message);

        $campaign->markAsReady();

        self::assertSame(PreparationStatusEnum::Ready, $campaign->getPreparationStatus());
        self::assertNotNull($campaign->getPreparedAt());
        self::assertTrue($campaign->canSend());
    }

    public function testCanSendWhenMessageAlreadySentReturnsFalse(): void
    {
        $message = $this->createStub(AdherentMessage::class);
        $message->method('isSent')->willReturn(true);
        $campaign = $this->createCampaign($message);

        $campaign->markAsReady();

        self::assertFalse($campaign->canSend());
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

    private function createCampaign(?AdherentMessageInterface $message = null): MailchimpCampaign
    {
        return new MailchimpCampaign($message ?? $this->createStub(AdherentMessageInterface::class));
    }

    private function createUser(): Adherent
    {
        return $this->createStub(Adherent::class);
    }
}
