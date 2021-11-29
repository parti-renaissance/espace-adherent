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
                $campaignHistory->getCampaign()
            ));

            $this->em->persist($buildingBlockStats);
        }
        $buildingBlockStats->setStatus(BuildingStatusEnum::ONGOING);

        if (!$floorNumber = $campaignHistory->getFloor()) {
            if (!$floor = $buildingBlock->getFloorByNumber($floorNumber)) {
                $floor = new Floor($floorNumber, $buildingBlock);
                $floor->setCreatedByAdherent($createdBy);
                $buildingBlock->addFloor($floor);

                $this->em->persist($floor);
            }

            if (!$floorStats = $floor->findStatisticsForCampaign($campaign)) {
                $floor->addStatistic($floorStats = new FloorStatistics(
                    $floor,
                    $campaignHistory->getCampaign()
                ));

                $this->em->persist($floorStats);
            }
            $floorStats->setStatus(BuildingStatusEnum::ONGOING);
        }

        if (!$buildingStats = $building->findStatisticsForCampaign($campaign)) {
            $building->addStatistic($buildingStats = new BuildingStatistics(
                $building,
                $campaignHistory->getCampaign()
            ));

            $this->em->persist($buildingStats);
        }
        $buildingStats->setStatus(BuildingStatusEnum::ONGOING);

        $this->em->flush();
    }
}
