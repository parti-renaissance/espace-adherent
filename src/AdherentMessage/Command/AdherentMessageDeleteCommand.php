<?php

namespace App\AdherentMessage\Command;

use App\Mailchimp\CampaignMessageInterface;

class AdherentMessageDeleteCommand implements CampaignMessageInterface
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
}
