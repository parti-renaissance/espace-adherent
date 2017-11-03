<?php

namespace AppBundle\CitizenAction;

use AppBundle\Entity\Adherent;
use AppBundle\Repository\CitizenActionRepository;

class CitizenActionManager
{
    private $repository;

    public function __construct(CitizenActionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function removeOrganizerCitizenActions(Adherent $adherent): void
    {
        $this->repository->removeOrganizerEvents($adherent, CitizenActionRepository::TYPE_PAST, true);
        $this->repository->removeOrganizerEvents($adherent, CitizenActionRepository::TYPE_UPCOMING);
    }
}
