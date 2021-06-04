<?php

namespace App\Adherent\Unregistration\Handlers;

use App\Entity\Adherent;
use App\Repository\CauseEventRepository;
use App\Repository\CoalitionEventRepository;
use App\Repository\EventRepository;
use App\Repository\InstitutionalEventRepository;

class UpdateEventHandler implements UnregistrationAdherentHandlerInterface
{
    private $eventRepository;
    private $institutionalEventRepository;
    private $coalitionEventRepository;
    private $causeEventRepository;

    public function __construct(
        EventRepository $eventRepository,
        InstitutionalEventRepository $institutionalEventRepository,
        CoalitionEventRepository $coalitionEventRepository,
        CauseEventRepository $causeEventRepository
    ) {
        $this->eventRepository = $eventRepository;
        $this->institutionalEventRepository = $institutionalEventRepository;
        $this->coalitionEventRepository = $coalitionEventRepository;
        $this->causeEventRepository = $causeEventRepository;
    }

    public function supports(Adherent $adherent): bool
    {
        return true;
    }

    public function handle(Adherent $adherent): void
    {
        foreach ([
            $this->eventRepository,
            $this->institutionalEventRepository,
            $this->coalitionEventRepository,
            $this->causeEventRepository,
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
