<?php

namespace App\SmsCampaign\Command;

class SendSmsCampaignCommand
{
    private $smsCampaignId;

    public function __construct(int $smsCampaignId)
    {
        $this->smsCampaignId = $smsCampaignId;
    }

    public function getSmsCampaignId(): int
    {
        return $this->smsCampaignId;
    }
}
