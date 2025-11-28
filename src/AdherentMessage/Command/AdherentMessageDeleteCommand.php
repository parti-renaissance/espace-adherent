<?php

declare(strict_types=1);

namespace App\AdherentMessage\Command;

use App\Mailchimp\AbstractCampaignMessage;

class AdherentMessageDeleteCommand extends AbstractCampaignMessage
{
    private $campaignId;

    public function __construct(string $campaignId)
    {
        $this->campaignId = $campaignId;
    }

    public function getCampaignId(): string
    {
        return $this->campaignId;
    }

    public function getLockKey(): string
    {
        return 'delete_campaign_'.$this->campaignId;
    }
}
