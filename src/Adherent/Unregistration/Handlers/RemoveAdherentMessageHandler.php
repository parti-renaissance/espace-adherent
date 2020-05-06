<?php

namespace App\Adherent\Unregistration\Handlers;

use App\Entity\Adherent;
use App\Repository\AdherentMessageRepository;

class RemoveAdherentMessageHandler implements UnregistrationAdherentHandlerInterface
{
    private $repository;

    public function __construct(AdherentMessageRepository $repository)
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
