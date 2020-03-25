<?php

namespace AppBundle\Adherent\Unregistration\Handlers;

use AppBundle\Entity\Adherent;
use AppBundle\Repository\EventRegistrationRepository;

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
