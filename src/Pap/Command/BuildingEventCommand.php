<?php

declare(strict_types=1);

namespace App\Pap\Command;

use Symfony\Component\Uid\Uuid;

class BuildingEventCommand implements BuildingEventCommandInterface
{
    private Uuid $buildingUuid;
    private Uuid $campaignUuid;

    public function __construct(Uuid $buildingUuid, Uuid $campaignUuid)
    {
        $this->buildingUuid = $buildingUuid;
        $this->campaignUuid = $campaignUuid;
    }

    public function getBuildingUuid(): Uuid
    {
        return $this->buildingUuid;
    }

    public function getCampaignUuid(): Uuid
    {
        return $this->campaignUuid;
    }
}
