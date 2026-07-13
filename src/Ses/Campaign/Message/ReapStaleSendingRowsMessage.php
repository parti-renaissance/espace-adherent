<?php

declare(strict_types=1);

namespace App\Ses\Campaign\Message;

class ReapStaleSendingRowsMessage implements SesCampaignMessageInterface
{
    public function __construct(
        public readonly int $campaignId,
        // Watchdog cycles already run for this campaign, so a campaign that never completes cannot re-arm
        // itself forever.
        public readonly int $cycle = 0,
    ) {
    }
}
