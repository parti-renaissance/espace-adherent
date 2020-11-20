<?php

namespace App\Controller\EnMarche\EventManager;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Adherent;
use App\Event\EventManagerSpaceEnum;
use App\Repository\EventRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-senateur", name="app_senator_event_manager_")
 *
 * @Security("is_granted('ROLE_SENATOR') or (is_granted('ROLE_DELEGATED_SENATOR') and is_granted('HAS_DELEGATED_ACCESS_EVENTS'))")
 */
class SenatorEventManagerController extends AbstractEventManagerController
{
    private $repository;

    public function __construct(EventRepository $repository)
    {
        $this->repository = $repository;
    }

    protected function getSpaceType(): string
    {
        return EventManagerSpaceEnum::SENATOR;
    }

    protected function getEventsPaginator(Adherent $adherent, string $type = null, int $page = 1): PaginatorInterface
    {
        if (AbstractEventManagerController::EVENTS_TYPE_ALL === $type) {
            return $this->repository->findManagedByPaginator([$adherent->getSenatorArea()->getDepartmentTag()], $page);
        }

        return $this->repository->findEventsByOrganizerPaginator($adherent, $page);
    }
}
