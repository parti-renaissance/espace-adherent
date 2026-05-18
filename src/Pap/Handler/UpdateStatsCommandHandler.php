<?php

declare(strict_types=1);

namespace App\Pap\Handler;

use App\Pap\BuildingStatisticsManager;
use App\Pap\Command\UpdateStatsCommand;
use App\Repository\Pap\BuildingRepository;
use App\Repository\Pap\CampaignRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateStatsCommandHandler
{
    private CampaignRepository $campaignRepository;
    private BuildingRepository $buildingRepository;
    private BuildingStatisticsManager $buildingStatisticsManager;

    public function __construct(
        CampaignRepository $campaignRepository,
        BuildingRepository $buildingRepository,
        BuildingStatisticsManager $buildingStatisticsManager,
    ) {
        $this->campaignRepository = $campaignRepository;
        $this->buildingRepository = $buildingRepository;
        $this->buildingStatisticsManager = $buildingStatisticsManager;
    }

    public function __invoke(UpdateStatsCommand $command): void
    {
        if (!$campaign = $this->campaignRepository->find($command->getCampaignId())) {
            return;
        }

        if (!$building = $this->buildingRepository->find($command->getBuildingId())) {
            return;
        }

        $this->buildingStatisticsManager->updateStats($building, $campaign);
    }
}
