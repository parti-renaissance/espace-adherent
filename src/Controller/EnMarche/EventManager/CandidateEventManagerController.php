<?php

namespace App\Controller\EnMarche\EventManager;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\AdherentSpace\AdherentSpaceEnum;
use App\Entity\Adherent;
use App\Entity\Event\EventGroupCategory;
use App\Event\EventManagerSpaceEnum;
use App\Geo\ManagedZoneProvider;
use App\Repository\Event\EventRepository;
use App\Repository\EventGroupCategoryRepository;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('ROLE_CANDIDATE') or (is_granted('ROLE_DELEGATED_CANDIDATE') and is_granted('HAS_DELEGATED_ACCESS_EVENTS'))"))]
#[Route(path: '/espace-candidat', name: 'app_candidate_event_manager')]
class CandidateEventManagerController extends AbstractEventManagerController
{
    private $repository;

    /**
     * @var ManagedZoneProvider
     */
    private $managedZoneProvider;

    private $eventGroupCategoryRepository;

    public function __construct(
        EventRepository $repository,
        ManagedZoneProvider $managedZoneProvider,
        EventGroupCategoryRepository $eventGroupCategoryRepository,
    ) {
        $this->repository = $repository;
        $this->managedZoneProvider = $managedZoneProvider;
        $this->eventGroupCategoryRepository = $eventGroupCategoryRepository;
    }

    protected function getSpaceType(): string
    {
        return EventManagerSpaceEnum::CANDIDATE;
    }

    protected function getEventsPaginator(Adherent $adherent, ?string $type = null, int $page = 1): PaginatorInterface
    {
        if (AbstractEventManagerController::EVENTS_TYPE_ALL === $type) {
            $managedZones = $this->managedZoneProvider->getManagedZones($adherent, AdherentSpaceEnum::CANDIDATE);

            return $this->repository->findManagedByPaginator($managedZones, $page);
        }

        return $this->repository->findEventsByOrganizerPaginator($adherent, $page, 50, EventGroupCategory::CAMPAIGN_EVENTS);
    }

    protected function getEventGroupCategory(): ?EventGroupCategory
    {
        return $this->eventGroupCategoryRepository->findOneBy(['slug' => EventGroupCategory::CAMPAIGN_EVENTS]);
    }

    protected function shareByEmail(): bool
    {
        return false;
    }
}
