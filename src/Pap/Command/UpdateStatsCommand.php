<?php

declare(strict_types=1);

namespace App\Pap\Command;

class UpdateStatsCommand implements AsynchronousMessageInterface
{
    private int $buildingId;
    private int $campaignId;

    public function __construct(int $buildingId, int $campaignId)
    {
        $this->buildingId = $buildingId;
        $this->campaignId = $campaignId;
    }

    public function getBuildingId(): int
    {
        return $this->buildingId;
    }

    public function getCampaignId(): int
    {
        return $this->campaignId;
    }
}
