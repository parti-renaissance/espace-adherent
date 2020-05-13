<?php

namespace App\Adherent\Unregistration\Handlers;

use App\Entity\Adherent;
use App\Repository\EventRegistrationRepository;

class UpdateEventRegistrationHandler implements UnregistrationAdherentHandlerInterface
{
    private $repository;

    public function __construct(EventRegistrationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function supports(Adherent $adherent): bool
    {
        return true;
    }

    public function handle(Adherent $adherent): void
    {
        $this->repository->anonymizeAdherentRegistrations($adherent);
    }
}
