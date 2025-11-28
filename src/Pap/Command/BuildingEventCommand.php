<?php

declare(strict_types=1);

namespace App\Pap\Command;

use Ramsey\Uuid\UuidInterface;

class BuildingEventCommand implements BuildingEventCommandInterface
{
    private UuidInterface $buildingUuid;
    private UuidInterface $campaignUuid;

    public function __construct(UuidInterface $buildingUuid, UuidInterface $campaignUuid)
    {
        $this->buildingUuid = $buildingUuid;
        $this->campaignUuid = $campaignUuid;
    }

    public function getBuildingUuid(): UuidInterface
    {
        return $this->buildingUuid;
    }

    public function getCampaignUuid(): UuidInterface
    {
        return $this->campaignUuid;
    }
}
