<?php

namespace App\Pap\Command;

use App\Messenger\Message\AsynchronousMessageInterface;
use Ramsey\Uuid\UuidInterface;

class UpdateCampaignAddressInfoCommand implements AsynchronousMessageInterface
{
    private UuidInterface $campaignUuid;

    public function __construct(UuidInterface $campaignUuid)
    {
        $this->campaignUuid = $campaignUuid;
    }

    public function getCampaignUuid(): UuidInterface
    {
        return $this->campaignUuid;
    }
}
