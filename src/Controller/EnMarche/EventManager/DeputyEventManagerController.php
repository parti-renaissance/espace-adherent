<?php

namespace App\Controller\EnMarche\EventManager;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\AdherentSpace\AdherentSpaceEnum;
use App\Entity\Adherent;
use App\Event\EventManagerSpaceEnum;
use App\Geo\ManagedZoneProvider;
use App\Repository\Event\BaseEventRepository;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('ROLE_DEPUTY') or (is_granted('ROLE_DELEGATED_DEPUTY') and is_granted('HAS_DELEGATED_ACCESS_EVENTS'))"))]
#[Route(path: '/espace-depute', name: 'app_deputy_event_manager')]
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

    protected function getEventsPaginator(Adherent $adherent, ?string $type = null, int $page = 1): PaginatorInterface
    {
        if (AbstractEventManagerController::EVENTS_TYPE_ALL === $type) {
            $managedZones = $this->managedZoneProvider->getManagedZones($adherent, AdherentSpaceEnum::DEPUTY);

            return $this->repository->findManagedByPaginator($managedZones, $page);
        }

        return $this->repository->findEventsByOrganizerPaginator($adherent, $page);
    }
}
