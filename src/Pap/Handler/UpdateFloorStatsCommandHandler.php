<?php

namespace App\Pap\Handler;

use App\Entity\Pap\CampaignHistory;
use App\Pap\Command\UpdateStatsCommand;
use App\Repository\Jecoute\DataSurveyRepository;
use App\Repository\Pap\CampaignHistoryRepository;
use App\Repository\Pap\FloorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UpdateFloorStatsCommandHandler implements MessageHandlerInterface
{
    private CampaignHistoryRepository $campaignHistoryRepository;
    private DataSurveyRepository $dataSurveyRepository;
    private FloorRepository $floorRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        CampaignHistoryRepository $campaignHistoryRepository,
        DataSurveyRepository $dataSurveyRepository,
        FloorRepository $floorRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->campaignHistoryRepository = $campaignHistoryRepository;
        $this->dataSurveyRepository = $dataSurveyRepository;
        $this->floorRepository = $floorRepository;
        $this->entityManager = $entityManager;
    }

    public function __invoke(UpdateStatsCommand $command): void
    {
        /** @var CampaignHistory $campaignHistory */
        if (!$campaignHistory = $this->campaignHistoryRepository->findOneByUuid($command->getCampaignHistoryUuid())) {
            return;
        }

        $floor = $this->floorRepository->findOneInBuilding(
            $campaignHistory->getBuilding(),
            $campaignHistory->getBuildingBlock(),
            $campaignHistory->getFloor()
        );

        if (!$floor) {
            throw new \RuntimeException(sprintf('Floor "%s" not found for building block "%s" in building "%s"', $campaignHistory->getFloor(), $campaignHistory->getBuildingBlock(), $campaignHistory->getBuilding()->getId(), ));
        }
        $stats = $floor->findStatisticsForCampaign($campaignHistory->getCampaign());

        if (!$stats) {
            throw new \RuntimeException(sprintf('No statistics found for floor "%s"', $floor->getUuid()));
        }
        $stats->setNbSurveys($this->dataSurveyRepository->findNbSurveysForFloor(
            $campaignHistory->getBuilding(),
            $campaignHistory->getBuildingBlock(),
            $campaignHistory->getFloor()
        ));
        $stats->setDoors($this->campaignHistoryRepository->findDoorsForFloor(
            $campaignHistory->getBuilding(),
            $campaignHistory->getBuildingBlock(),
            $campaignHistory->getFloor()
        ));

        $this->entityManager->flush();
    }
}
