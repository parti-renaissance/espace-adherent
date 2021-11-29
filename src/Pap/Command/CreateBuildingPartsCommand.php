<?php

namespace App\Pap\Command;

use App\Messenger\Message\AsynchronousMessageInterface;
use Ramsey\Uuid\UuidInterface;

class CreateBuildingPartsCommand implements AsynchronousMessageInterface
{
    private UuidInterface $campaignHistoryUuid;

    public function __construct(UuidInterface $campaignHistoryUuid)
    {
        $this->campaignHistoryUuid = $campaignHistoryUuid;
    }

    public function getCampaignHistoryUuid(): UuidInterface
    {
        return $this->campaignHistoryUuid;
    }
}
