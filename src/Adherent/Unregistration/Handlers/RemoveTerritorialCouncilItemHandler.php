<?php

namespace App\Adherent\Unregistration\Handlers;

use App\Entity\Adherent;
use App\Repository\TerritorialCouncil\TerritorialCouncilFeedItemRepository;

class RemoveTerritorialCouncilItemHandler implements UnregistrationAdherentHandlerInterface
{
    private $repository;

    public function __construct(TerritorialCouncilFeedItemRepository $repository)
    {
        $this->repository = $repository;
    }

    public function supports(Adherent $adherent): bool
    {
        return true;
    }

    public function handle(Adherent $adherent): void
    {
        $this->repository->removeAuthorItems($adherent);
    }
}
