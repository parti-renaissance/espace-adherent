<?php

namespace App\Controller\EnMarche\EventManager;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Adherent;
use App\Entity\EventGroupCategory;
use App\Event\EventManagerSpaceEnum;
use App\Repository\EventGroupCategoryRepository;
use App\Repository\EventRepository;
use App\Repository\ReferentTagRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-candidat", name="app_candidate_event_manager_")
 *
 * @Security("is_granted('ROLE_CANDIDATE') or (is_granted('ROLE_DELEGATED_CANDIDATE') and is_granted('HAS_DELEGATED_ACCESS_EVENTS'))")
 */
class CandidateEventManagerController extends AbstractEventManagerController
{
    private $repository;
    private $referentTagRepository;
    private $eventGroupCategoryRepository;

    public function __construct(
        EventRepository $repository,
        ReferentTagRepository $referentTagRepository,
        EventGroupCategoryRepository $eventGroupCategoryRepository
    ) {
        $this->repository = $repository;
        $this->referentTagRepository = $referentTagRepository;
        $this->eventGroupCategoryRepository = $eventGroupCategoryRepository;
    }

    protected function getSpaceType(): string
    {
        return EventManagerSpaceEnum::CANDIDATE;
    }

    protected function getEventsPaginator(Adherent $adherent, string $type = null, int $page = 1): PaginatorInterface
    {
        if (AbstractEventManagerController::EVENTS_TYPE_ALL === $type) {
            return $this->repository->findManagedByPaginator(
                $this->referentTagRepository->findByZones([$adherent->getCandidateManagedArea()->getZone()]),
                $page
            );
        }

        return $this->repository->findEventsByOrganizerAndGroupCategoryPaginator($adherent, EventGroupCategory::CAMPAIGN_EVENTS, $page);
    }

    protected function getEventGroupCategory(): ?EventGroupCategory
    {
        return $this->eventGroupCategoryRepository->findOneBy(['slug' => EventGroupCategory::CAMPAIGN_EVENTS]);
    }
}
