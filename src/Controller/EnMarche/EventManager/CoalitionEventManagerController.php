<?php

namespace App\Controller\EnMarche\EventManager;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Adherent;
use App\Entity\Event\CoalitionEvent;
use App\Event\EventManagerSpaceEnum;
use App\Repository\CoalitionEventRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-coalition", name="app_coalition_moderator_event_manager")
 *
 * @Security("is_granted('ROLE_COALITION_MODERATOR')")
 */
class CoalitionEventManagerController extends AbstractEventManagerController
{
    private $repository;

    public function __construct(CoalitionEventRepository $repository)
    {
        $this->repository = $repository;
    }

    protected function getSpaceType(): string
    {
        return EventManagerSpaceEnum::COALITION_MODERATOR;
    }

    protected function getEventsPaginator(Adherent $adherent, string $type = null, int $page = 1): PaginatorInterface
    {
        if (AbstractEventManagerController::EVENTS_TYPE_ALL === $type) {
            return $this->repository->findAllPublished($page);
        }

        return $this->repository->findEventsByOrganizerPaginator($adherent, $page);
    }

    protected function getEventClassName(): string
    {
        return CoalitionEvent::class;
    }

    protected function shareByEmail(): bool
    {
        return false;
    }
}
