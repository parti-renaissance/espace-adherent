<?php

namespace App\Controller\EnMarche\EventManager;

use App\Entity\Adherent;
use App\Event\EventManagerSpaceEnum;
use App\Repository\EventRepository;
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

    protected function getEvents(Adherent $adherent, string $type = null): array
    {
        if (AbstractEventManagerController::EVENTS_TYPE_ALL === $type) {
            return $this->repository->findManagedBy($adherent->getManagedArea()->getTags()->toArray());
        }

        return $this->repository->findEventsByOrganizer($adherent);
    }
}
