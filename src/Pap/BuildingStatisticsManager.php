<?php

declare(strict_types=1);

namespace App\Pap;

use App\Entity\Pap\Building;
use App\Entity\Pap\BuildingBlockStatistics;
use App\Entity\Pap\BuildingStatistics;
use App\Entity\Pap\Campaign;
use App\Entity\Pap\FloorStatistics;
use App\Repository\Jecoute\DataSurveyRepository;
use App\Repository\Pap\CampaignHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;

class BuildingStatisticsManager
{
    private EntityManagerInterface $em;
    private DataSurveyRepository $dataSurveyRepository;
    private CampaignHistoryRepository $campaignHistoryRepository;

    public function __construct(
        EntityManagerInterface $em,
        DataSurveyRepository $dataSurveyRepository,
        CampaignHistoryRepository $campaignHistoryRepository,
    ) {
        $this->em = $em;
        $this->dataSurveyRepository = $dataSurveyRepository;
        $this->campaignHistoryRepository = $campaignHistoryRepository;
    }

    public function updateStats(Building $building, Campaign $campaign): BuildingStatistics
    {
        // building stats
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

        if ($campaignHistory = $this->campaignHistoryRepository->findLastFor($building, $campaign)) {
            $buildingStats->setLastPassage($campaignHistory->getUpdatedAt());
            $buildingStats->setLastPassageDoneBy($campaignHistory->getQuestioner());
        }

        $buildingStats->setNbVisitedDoors($this->campaignHistoryRepository->countDoorsForBuilding($building));
        $buildingStats->setNbSurveys($this->dataSurveyRepository->countSurveysForBuilding($building));

        // building block stats
        foreach ($building->getBuildingBlocks() as $buildingBlock) {
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

            // floor stats
            foreach ($buildingBlock->getFloors() as $floor) {
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

                $floorStats->setNbSurveys($this->dataSurveyRepository->countSurveysForBuilding(
                    $building,
                    $buildingBlock->getName(),
                    $floor->getNumber()
                ));
                $floorStats->setVisitedDoors($this->campaignHistoryRepository->findDoorsForFloor(
                    $building,
                    $buildingBlock->getName(),
                    $floor->getNumber()
                ));
            }
        }

        $this->em->flush();

        return $buildingStats;
    }
}
