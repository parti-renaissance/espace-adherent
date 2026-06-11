<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Command;

use App\Mailchimp\CampaignMessageInterface;
use App\Messenger\Message\LockableMessageInterface;

/**
 * Post-send delivery verification for a Mailchimp campaign.
 *
 * Routed to the `mailchimp_campaign` queue via {@see CampaignMessageInterface}. Lockable so that
 * concurrent re-deliveries of the same campaign cannot race.
 */
class VerifyCampaignDeliveryCommand implements CampaignMessageInterface, LockableMessageInterface
{
    public function __construct(
        public readonly int $campaignId,
        // Confirmation window: advances only once the campaign reaches a terminal "sent" — drives the
        // ~30 min wait for the delivery report to confirm emails_sent.
        public readonly int $countRetry = 0,
        // Sending window: advances while the campaign is still "sending" (a large national send can
        // legitimately stay there well past the confirmation window). Kept separate so the
        // confirmation window only starts at the first "sent".
        public readonly int $sendingRetry = 0,
    ) {
    }

    public function getLockKey(): string
    {
        return 'verify_campaign_delivery_'.$this->campaignId;
    }

    public function getLockTtl(): int
    {
        return 1800;
    }

    public function isLockBlocking(): bool
    {
        return true;
    }
}
