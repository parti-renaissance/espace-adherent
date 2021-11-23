<?php

namespace App\Pap;

use App\Entity\Pap\BuildingBlock;
use App\Entity\Pap\BuildingBlockStatistics;
use App\Entity\Pap\CampaignHistory;
use App\Entity\Pap\Floor;
use App\Entity\Pap\FloorStatistics;
use Doctrine\ORM\EntityManagerInterface;

class CampaignHistoryManager
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createBuildingParts(CampaignHistory $campaignHistory): void
    {
        $createdBy = $campaignHistory->getQuestioner();
        $building = $campaignHistory->getBuilding();
        if (!$buildingBlockName = $campaignHistory->getBuildingBlock()) {
            return;
        }

        $buildingBlock = $building->getBuildingBlockByName($buildingBlockName);
        if (!$buildingBlock) {
            $buildingBlock = new BuildingBlock($buildingBlockName, $building);
            $buildingBlock->setCreatedByAdherent($createdBy);
            $building->addBuildingBlock($buildingBlock);

            $bbStats = new BuildingBlockStatistics(
                $buildingBlock,
                $campaignHistory->getCampaign(),
                BuildingStatusEnum::ONGOING
            );
            $buildingBlock->addStatistic($bbStats);

            $this->em->persist($buildingBlock);
        }

        if (!$floorNumber = $campaignHistory->getFloor()) {
            return;
        }

        $floor = $buildingBlock->getFloorByNumber($floorNumber);
        if (!$floor) {
            $floor = new Floor($floorNumber, $buildingBlock);
            $floor->setCreatedByAdherent($createdBy);
            $buildingBlock->addFloor($floor);

            $floorStats = new FloorStatistics(
                $floor,
                $campaignHistory->getCampaign(),
                BuildingStatusEnum::ONGOING
            );
            $floor->addStatistic($floorStats);

            $this->em->persist($floor);
        }

        $this->em->flush();
    }
}
