<?php

namespace App\Controller\EnMarche\EventManager;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\AdherentSpace\AdherentSpaceEnum;
use App\Entity\Adherent;
use App\Event\EventManagerSpaceEnum;
use App\Geo\ManagedZoneProvider;
use App\Repository\Event\BaseEventRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-senateur", name="app_senator_event_manager")
 *
 * @Security("is_granted('ROLE_SENATOR') or (is_granted('ROLE_DELEGATED_SENATOR') and is_granted('HAS_DELEGATED_ACCESS_EVENTS'))")
 */
class SenatorEventManagerController extends AbstractEventManagerController
{
    private $repository;

    /**
     * @var ManagedZoneProvider
     */
    private $managedZoneProvider;

    public function __construct(BaseEventRepository $repository, ManagedZoneProvider $managedZoneProvider)
    {
        $this->repository = $repository;
        $this->managedZoneProvider = $managedZoneProvider;
    }

    protected function getSpaceType(): string
    {
        return EventManagerSpaceEnum::SENATOR;
    }

    protected function getEventsPaginator(Adherent $adherent, string $type = null, int $page = 1): PaginatorInterface
    {
        if (AbstractEventManagerController::EVENTS_TYPE_ALL === $type) {
            $managedZones = $this->managedZoneProvider->getManagedZones($adherent, AdherentSpaceEnum::SENATOR);

            return $this->repository->findManagedByPaginator($managedZones, $page);
        }

        return $this->repository->findEventsByOrganizerPaginator($adherent, $page);
    }
}
