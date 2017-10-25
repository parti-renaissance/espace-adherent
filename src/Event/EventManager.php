<?php

namespace AppBundle\Event;

use AppBundle\Entity\Adherent;
use AppBundle\Repository\EventRepository;

class EventManager
{
    private $repository;

    public function __construct(EventRepository $repository)
    {
        $this->repository = $repository;
    }

    public function removeOrganizerEvents(Adherent $adherent): void
    {
        $this->repository->removeOrganizerEvents($adherent, EventRepository::TYPE_PAST, true);
        $this->repository->removeOrganizerEvents($adherent, EventRepository::TYPE_UPCOMING);
    }
}
