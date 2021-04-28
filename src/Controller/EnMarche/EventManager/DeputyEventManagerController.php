<?php

namespace App\Controller\EnMarche\EventManager;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Adherent;
use App\Event\EventManagerSpaceEnum;
use App\Geo\ManagedZoneProvider;
use App\Repository\Event\BaseEventRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-depute", name="app_deputy_event_manager")
 *
 * @Security("is_granted('ROLE_DEPUTY') or (is_granted('ROLE_DELEGATED_DEPUTY') and is_granted('HAS_DELEGATED_ACCESS_EVENTS'))")
 */
class DeputyEventManagerController extends AbstractEventManagerController
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
        return EventManagerSpaceEnum::DEPUTY;
    }

    protected function getEventsPaginator(Adherent $adherent, string $type = null, int $page = 1): PaginatorInterface
    {
        if (AbstractEventManagerController::EVENTS_TYPE_ALL === $type) {
            $managedZones = $this->managedZoneProvider->getManagedZones($adherent, ManagedZoneProvider::DEPUTY);

            return $this->repository->findManagedByPaginator($managedZones, $page);
        }

        return $this->repository->findEventsByOrganizerPaginator($adherent, $page);
    }
}
