<?php

declare(strict_types=1);

namespace App\Pap\Handler;

use App\Entity\Pap\CampaignHistory;
use App\Pap\CampaignHistoryManager;
use App\Pap\Command\CreateBuildingPartsCommand;
use App\Pap\Command\UpdateStatsCommand;
use App\Repository\Pap\CampaignHistoryRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class CreateBuildingPartsCommandHandler
{
    private CampaignHistoryRepository $repository;
    private CampaignHistoryManager $campaignHistoryManager;
    private MessageBusInterface $bus;

    public function __construct(
        CampaignHistoryRepository $repository,
        CampaignHistoryManager $campaignHistoryManager,
        MessageBusInterface $bus,
    ) {
        $this->repository = $repository;
        $this->campaignHistoryManager = $campaignHistoryManager;
        $this->bus = $bus;
    }

    public function __invoke(CreateBuildingPartsCommand $command): void
    {
        /** @var CampaignHistory $campaignHistory */
        if (!$campaignHistory = $this->repository->findOneByUuid($command->getCampaignHistoryUuid())) {
            return;
        }

        $this->campaignHistoryManager->createBuildingParts($campaignHistory);
        $this->bus->dispatch(
            new UpdateStatsCommand(
                $campaignHistory->getBuilding()->getId(),
                $campaignHistory->getCampaign()->getId()
            )
        );
    }
}
