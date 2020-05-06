<?php

namespace App\Adherent\Unregistration\Handlers;

use App\Entity\Adherent;
use App\Repository\CitizenActionRepository;
use App\Repository\EventRepository;
use App\Repository\InstitutionalEventRepository;

class UpdateEventHandler implements UnregistrationAdherentHandlerInterface
{
    private $eventRepository;
    private $citizenActionRepository;
    private $institutionalEventRepository;

    public function __construct(
        EventRepository $eventRepository,
        CitizenActionRepository $citizenActionRepository,
        InstitutionalEventRepository $institutionalEventRepository
    ) {
        $this->eventRepository = $eventRepository;
        $this->citizenActionRepository = $citizenActionRepository;
        $this->institutionalEventRepository = $institutionalEventRepository;
    }

    public function supports(Adherent $adherent): bool
    {
        return true;
    }

    public function handle(Adherent $adherent): void
    {
        foreach ([
            $this->eventRepository,
            $this->citizenActionRepository,
            $this->institutionalEventRepository,
        ] as $repository) {
            $this->updateEvents($repository, $adherent);
        }
    }

    private function updateEvents(EventRepository $repository, Adherent $adherent): void
    {
        $repository->removeOrganizerEvents($adherent, EventRepository::TYPE_PAST, true);
        $repository->removeOrganizerEvents($adherent, EventRepository::TYPE_UPCOMING);
    }
}
