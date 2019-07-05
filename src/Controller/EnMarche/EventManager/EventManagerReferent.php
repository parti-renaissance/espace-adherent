<?php

namespace AppBundle\Controller\EnMarche\EventManager;

use AppBundle\Event\EventManagerSpaceEnum;
use AppBundle\Repository\EventRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-referent", name="app_referent_event_manager_")
 *
 * @Security("is_granted('ROLE_REFERENT')")
 */
class EventManagerReferent extends AbstractEventManagerController
{
    protected function getSpaceType(): string
    {
        return EventManagerSpaceEnum::REFERENT;
    }

    protected function getEvents(EventRepository $eventRepository, string $type = null): array
    {
        if (AbstractEventManagerController::EVENTS_TYPE_ALL === $type) {
            return $eventRepository->findManagedBy($this->getUser());
        } else {
            return $eventRepository->findEventsByOrganizer($this->getUser());
        }
    }
}
