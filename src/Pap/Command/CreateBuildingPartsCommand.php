<?php

declare(strict_types=1);

namespace App\Pap\Command;

use Symfony\Component\Uid\Uuid;

class CreateBuildingPartsCommand implements AsynchronousMessageInterface
{
    private Uuid $campaignHistoryUuid;

    public function __construct(Uuid $campaignHistoryUuid)
    {
        $this->campaignHistoryUuid = $campaignHistoryUuid;
    }

    public function getCampaignHistoryUuid(): Uuid
    {
        return $this->campaignHistoryUuid;
    }
}
