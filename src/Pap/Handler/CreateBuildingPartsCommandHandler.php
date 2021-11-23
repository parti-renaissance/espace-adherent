<?php

namespace App\Pap\Handler;

use App\Entity\Pap\CampaignHistory;
use App\Pap\CampaignHistoryManager;
use App\Pap\Command\CreateBuildingPartsCommand;
use App\Repository\Pap\CampaignHistoryRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CreateBuildingPartsCommandHandler implements MessageHandlerInterface
{
    private CampaignHistoryRepository $repository;
    private CampaignHistoryManager $campaignHistoryManager;

    public function __construct(CampaignHistoryRepository $repository, CampaignHistoryManager $campaignHistoryManager)
    {
        $this->repository = $repository;
        $this->campaignHistoryManager = $campaignHistoryManager;
    }

    public function __invoke(CreateBuildingPartsCommand $command): void
    {
        /** @var CampaignHistory $campaignHistory */
        if (!$campaignHistory = $this->repository->findOneByUuid($command->getCampaignHistoryUuid())) {
            return;
        }

        $this->campaignHistoryManager->createBuildingParts($campaignHistory);
    }
}
