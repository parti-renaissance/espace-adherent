<?php

namespace App\Adherent\Unregistration\Handlers;

use App\Entity\Adherent;
use App\Repository\Event\EventRepository;

class UpdateEventHandler implements UnregistrationAdherentHandlerInterface
{
    public function __construct(private readonly EventRepository $repository)
    {
    }

    public function supports(Adherent $adherent): bool
    {
        return true;
    }

    public function handle(Adherent $adherent): void
    {
        $this->repository->removeOrganizerEvents($adherent, EventRepository::TYPE_PAST, true);
        $this->repository->removeOrganizerEvents($adherent, EventRepository::TYPE_UPCOMING);
    }
}
