<?php

declare(strict_types=1);

namespace App\AdherentMessage\Command;

use App\Mailchimp\AbstractCampaignMessage;

class AdherentMessageDeleteCommand extends AbstractCampaignMessage
{
    public function __construct(
        private readonly string $campaignId,
        private readonly ?int $staticSegmentId = null,
    ) {
    }

    public function getCampaignId(): string
    {
        return $this->campaignId;
    }

    public function getStaticSegmentId(): ?int
    {
        return $this->staticSegmentId;
    }

    public function getLockKey(): string
    {
        return 'delete_campaign_'.$this->campaignId;
    }
}
