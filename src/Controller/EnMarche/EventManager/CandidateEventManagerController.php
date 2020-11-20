<?php

namespace App\Controller\EnMarche\EventManager;

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

    protected function getEvents(Adherent $adherent, string $type = null): array
    {
        if (AbstractEventManagerController::EVENTS_TYPE_ALL === $type) {
            return $this->repository->findManagedBy(
                $this->referentTagRepository->findByZones([$adherent->getCandidateManagedArea()->getZone()])
            );
        }

        return $this->repository->findEventsByOrganizerAndGroupCategory($adherent, EventGroupCategory::CAMPAIGN_EVENTS);
    }

    protected function getEventGroupCategory(): ?EventGroupCategory
    {
        return $this->eventGroupCategoryRepository->findOneBy(['slug' => EventGroupCategory::CAMPAIGN_EVENTS]);
    }
}
