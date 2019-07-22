<?php

namespace AppBundle\Controller\EnMarche\EventManager;

use AppBundle\Entity\Event;
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
    private $repository;

    public function __construct(EventRepository $repository)
    {
        $this->repository = $repository;
    }

    protected function getSpaceType(): string
    {
        return EventManagerSpaceEnum::REFERENT;
    }

    protected function getEvents(string $type = null): array
    {
        if (AbstractEventManagerController::EVENTS_TYPE_ALL === $type) {
            return $this->repository->findManagedBy($this->getUser());
        }

        return $this->repository->findEventsByOrganizer($this->getUser());
    }

    protected function getEventClassName(): string
    {
        return Event::class;
    }
}
