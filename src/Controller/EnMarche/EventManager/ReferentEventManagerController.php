<?php

namespace AppBundle\Controller\EnMarche\EventManager;

use AppBundle\Entity\Adherent;
use AppBundle\Event\EventManagerSpaceEnum;
use AppBundle\Repository\EventRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-referent", name="app_referent_event_manager_")
 *
 * @Security("is_granted('ROLE_REFERENT')")
 */
class ReferentEventManagerController extends AbstractEventManagerController
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
        /** @var Adherent $referent */
        $referent = $this->getUser();

        if (AbstractEventManagerController::EVENTS_TYPE_ALL === $type) {
            return $this->repository->findManagedBy($referent->getManagedArea()->getTags()->toArray());
        }

        return $this->repository->findEventsByOrganizer($referent);
    }
}
