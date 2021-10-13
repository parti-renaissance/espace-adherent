<?php

namespace App\Adherent\Unregistration\Handlers;

use App\Entity\Adherent;
use App\Event\EventTypeEnum;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;

class UpdateEventHandler implements UnregistrationAdherentHandlerInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function supports(Adherent $adherent): bool
    {
        return true;
    }

    public function handle(Adherent $adherent): void
    {
        foreach (EventTypeEnum::CLASSES as $class) {
            $this->updateEvents($this->entityManager->getRepository($class), $adherent);
        }
    }

    private function updateEvents(EventRepository $repository, Adherent $adherent): void
    {
        $repository->removeOrganizerEvents($adherent, EventRepository::TYPE_PAST, true);
        $repository->removeOrganizerEvents($adherent, EventRepository::TYPE_UPCOMING);
    }
}
