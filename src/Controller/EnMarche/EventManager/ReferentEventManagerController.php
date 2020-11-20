<?php

namespace App\Controller\EnMarche\EventManager;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Adherent;
use App\Event\EventManagerSpaceEnum;
use App\Repository\EventRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-referent", name="app_referent_event_manager_")
 *
 * @Security("is_granted('ROLE_REFERENT') or (is_granted('ROLE_DELEGATED_REFERENT') and is_granted('HAS_DELEGATED_ACCESS_EVENTS'))")
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

    protected function getEventsPaginator(Adherent $adherent, string $type = null, int $page = 1): PaginatorInterface
    {
        if (AbstractEventManagerController::EVENTS_TYPE_ALL === $type) {
            return $this->repository->findManagedByPaginator($adherent->getManagedArea()->getTags()->toArray(), $page);
        }

        return $this->repository->findEventsByOrganizerPaginator($adherent, $page);
    }
}
