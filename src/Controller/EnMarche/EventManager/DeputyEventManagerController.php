<?php

namespace App\Controller\EnMarche\EventManager;

use App\Entity\Adherent;
use App\Event\EventManagerSpaceEnum;
use App\Repository\EventRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-depute", name="app_deputy_event_manager_")
 *
 * @Security("is_granted('ROLE_DEPUTY')")
 */
class DeputyEventManagerController extends AbstractEventManagerController
{
    private $repository;

    public function __construct(EventRepository $repository)
    {
        $this->repository = $repository;
    }

    protected function getSpaceType(): string
    {
        return EventManagerSpaceEnum::DEPUTY;
    }

    protected function getEvents(string $type = null): array
    {
        /** @var Adherent $deputy */
        $deputy = $this->getUser();

        if (AbstractEventManagerController::EVENTS_TYPE_ALL === $type) {
            return $this->repository->findManagedBy([$deputy->getManagedDistrict()->getReferentTag()]);
        }

        return $this->repository->findEventsByOrganizer($deputy);
    }
}
