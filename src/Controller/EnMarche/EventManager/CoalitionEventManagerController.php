<?php

namespace App\Controller\EnMarche\EventManager;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\CoalitionEvent;
use App\Event\EventCommand;
use App\Event\EventManagerSpaceEnum;
use App\Form\Coalition\CoalitionEventType;
use App\Repository\CauseEventRepository;
use App\Repository\CoalitionEventRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_COALITION_MODERATOR")
 */
#[Route(path: '/espace-coalition', name: 'app_coalition_moderator_event_manager')]
class CoalitionEventManagerController extends AbstractEventManagerController
{
    public const EVENTS_TYPE_CAUSE = 'cause';
    public const EVENTS_TYPE_COALITION = 'coalition';

    private $causeEventRepository;
    private $coalitionEventRepository;

    public function __construct(
        CauseEventRepository $causeEventRepository,
        CoalitionEventRepository $coalitionEventRepository
    ) {
        $this->causeEventRepository = $causeEventRepository;
        $this->coalitionEventRepository = $coalitionEventRepository;
    }

    #[Route(path: '/evenements-de-coalitions', name: '_coalition_events', defaults: ['type' => CoalitionEventManagerController::EVENTS_TYPE_COALITION], methods: ['GET'])]
    #[Route(path: '/evenements-de-causes', name: '_cause_events', defaults: ['type' => CoalitionEventManagerController::EVENTS_TYPE_CAUSE], methods: ['GET'])]
    public function events(Request $request, string $type): Response
    {
        return $this->renderTemplate('event_manager/events_list.html.twig', [
            'events' => $this->getEventsPaginator(
                $this->getMainUser($request->getSession()),
                $type,
                $request->query->getInt('page', 1)
            ),
            'share_by_email' => $this->shareByEmail(),
        ]);
    }

    protected function createEventForm(EventCommand $command, BaseEvent $event = null): FormInterface
    {
        return $this->createForm(CoalitionEventType::class, $command);
    }

    protected function getSpaceType(): string
    {
        return EventManagerSpaceEnum::COALITION_MODERATOR;
    }

    protected function getEventsPaginator(Adherent $adherent, string $type = null, int $page = 1): PaginatorInterface
    {
        switch ($type) {
            case AbstractEventManagerController::EVENTS_TYPE_MINE:
                return $this->coalitionEventRepository->findEventsByOrganizerPaginator($adherent, $page);
            case CoalitionEventManagerController::EVENTS_TYPE_CAUSE:
                return $this->causeEventRepository->findAllPublished($page);
            default:
                return $this->coalitionEventRepository->findAllPublished($page);
        }
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
