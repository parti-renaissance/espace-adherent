<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Campaign\Audience;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Audience\AudienceCheckEnum;
use App\Mailchimp\Campaign\Audience\BlockReasonEnum;
use App\Mailchimp\Campaign\Audience\PreparationStatusEnum;
use App\Mailchimp\Campaign\Audience\SendStatusFactory;
use PHPUnit\Framework\TestCase;

class SendStatusFactoryTest extends TestCase
{
    public function testBuildReadyCampaignWithMatchAudienceCanSendIsTrue(): void
    {
        $message = new AdherentMessage();
        $campaign = new MailchimpCampaign($message);
        $campaign->markAsPreparing('user@example.com');
        $campaign->markAsReady(1000, 980, AudienceCheckEnum::Drift);

        $payload = new SendStatusFactory()->build($campaign);

        self::assertSame(PreparationStatusEnum::Ready->value, $payload['preparation_status']);
        self::assertSame(AudienceCheckEnum::Drift->value, $payload['audience_check']);
        self::assertNull($payload['block_reason']);
        self::assertTrue($payload['can_send']);
        self::assertFalse($payload['cancellation_requested']);
        self::assertSame(1000, $payload['counts']['expected']);
        self::assertSame(980, $payload['counts']['prepared']);
        self::assertSame(-20, $payload['counts']['diff']);
        self::assertNotNull($payload['prepared_at']);
        self::assertSame('user@example.com', $payload['blocking_user']);
    }

    public function testBuildMismatchAudienceCheckCanSendIsFalse(): void
    {
        $message = new AdherentMessage();
        $campaign = new MailchimpCampaign($message);
        $campaign->markAsPreparing('user@example.com');
        $campaign->markAsReady(1000, 500, AudienceCheckEnum::Mismatch);

        $payload = new SendStatusFactory()->build($campaign);

        self::assertFalse($payload['can_send']);
        self::assertSame(AudienceCheckEnum::Mismatch->value, $payload['audience_check']);
    }

    public function testBuildFailedCampaignBlockReasonExposed(): void
    {
        $message = new AdherentMessage();
        $campaign = new MailchimpCampaign($message);
        $campaign->markAsFailed(BlockReasonEnum::Empty, 'no recipients');

        $payload = new SendStatusFactory()->build($campaign);

        self::assertSame(PreparationStatusEnum::Failed->value, $payload['preparation_status']);
        self::assertSame(BlockReasonEnum::Empty->value, $payload['block_reason']);
        self::assertArrayNotHasKey('failure_detail', $payload, 'failure_detail must NOT leak to API payload (DESIGN §5.1)');
        self::assertFalse($payload['can_send']);
    }

    public function testBuildMessageAlreadySentCanSendIsFalse(): void
    {
        $message = new AdherentMessage();
        $campaign = new MailchimpCampaign($message);
        $campaign->markAsPreparing('user@example.com');
        $campaign->markAsReady(100, 100, AudienceCheckEnum::Match);

        // Mark the AdherentMessage as sent → can_send must drop to false
        $message->markAsSent();

        $payload = new SendStatusFactory()->build($campaign);

        self::assertFalse($payload['can_send']);
    }

    public function testBuildFreshCampaignCountsHaveNullDiff(): void
    {
        $message = new AdherentMessage();
        $campaign = new MailchimpCampaign($message);

        $payload = new SendStatusFactory()->build($campaign);

        self::assertSame(PreparationStatusEnum::NotStarted->value, $payload['preparation_status']);
        self::assertNull($payload['audience_check']);
        self::assertNull($payload['counts']['expected']);
        self::assertNull($payload['counts']['prepared']);
        self::assertNull($payload['counts']['diff']);
        self::assertSame(0, $payload['progress']['chunks_done']);
        self::assertNull($payload['blocking_user']);
        self::assertFalse($payload['can_send']);
    }

    public function testBuildCancellationRequestedFlagPropagatedToPayload(): void
    {
        $message = new AdherentMessage();
        $campaign = new MailchimpCampaign($message);
        $campaign->markAsPreparing('user@example.com');
        $campaign->requestCancellation();

        $payload = new SendStatusFactory()->build($campaign);

        self::assertTrue($payload['cancellation_requested']);
        self::assertSame(PreparationStatusEnum::Preparing->value, $payload['preparation_status']);
    }
}
