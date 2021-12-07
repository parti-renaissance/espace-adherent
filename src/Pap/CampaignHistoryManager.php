<?php

namespace App\Pap;

use App\Entity\Pap\BuildingBlock;
use App\Entity\Pap\BuildingBlockStatistics;
use App\Entity\Pap\BuildingStatistics;
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
        $campaign = $campaignHistory->getCampaign();
        if (!$buildingBlockName = $campaignHistory->getBuildingBlock()) {
            return;
        }

        if (!$buildingBlock = $building->getBuildingBlockByName($buildingBlockName)) {
            $buildingBlock = new BuildingBlock($buildingBlockName, $building);
            $buildingBlock->setCreatedByAdherent($createdBy);
            $building->addBuildingBlock($buildingBlock);

            $this->em->persist($buildingBlock);
        }

        if (!$buildingBlockStats = $buildingBlock->findStatisticsForCampaign($campaign)) {
            $buildingBlock->addStatistic($buildingBlockStats = new BuildingBlockStatistics(
                $buildingBlock,
                $campaign
            ));

            $this->em->persist($buildingBlockStats);
        }

        if ($buildingBlockStats->isTodo()) {
            $buildingBlockStats->setStatus(BuildingStatusEnum::ONGOING);
        }

        $floorNumber = $campaignHistory->getFloor();
        if (null !== $floorNumber) {
            if (!$floor = $buildingBlock->getFloorByNumber($floorNumber)) {
                $floor = new Floor($floorNumber, $buildingBlock);
                $floor->setCreatedByAdherent($createdBy);
                $buildingBlock->addFloor($floor);

                $this->em->persist($floor);
            }

            if (!$floorStats = $floor->findStatisticsForCampaign($campaign)) {
                $floor->addStatistic($floorStats = new FloorStatistics(
                    $floor,
                    $campaign
                ));

                $this->em->persist($floorStats);
            }

            if ($floorStats->isTodo()) {
                $floorStats->setStatus(BuildingStatusEnum::ONGOING);
            }
        }

        if (!$buildingStats = $building->findStatisticsForCampaign($campaign)) {
            $building->addStatistic($buildingStats = new BuildingStatistics(
                $building,
                $campaign
            ));

            $this->em->persist($buildingStats);
        }

        if ($buildingStats->isTodo()) {
            $buildingStats->setStatus(BuildingStatusEnum::ONGOING);
        }
        $buildingStats->setLastPassage($campaignHistory->getUpdatedAt());
        $buildingStats->setLastPassageDoneBy($createdBy);

        $this->em->flush();
    }
}
