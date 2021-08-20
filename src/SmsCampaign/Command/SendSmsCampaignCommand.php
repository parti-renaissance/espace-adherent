<?php

namespace App\SmsCampaign\Command;

use App\Messenger\Message\AsynchronousMessageInterface;

class SendSmsCampaignCommand implements AsynchronousMessageInterface
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
