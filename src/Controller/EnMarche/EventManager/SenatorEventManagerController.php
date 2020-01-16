<?php

namespace AppBundle\Controller\EnMarche\EventManager;

use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Entity\Adherent;
use AppBundle\Event\EventManagerSpaceEnum;
use AppBundle\Repository\EventRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-senateur", name="app_senator_event_manager_")
 *
 * @Security("is_granted('ROLE_SENATOR')")
 */
class SenatorEventManagerController extends AbstractEventManagerController
{
    use CanaryControllerTrait;

    private $repository;

    public function __construct(EventRepository $repository)
    {
        $this->repository = $repository;
    }

    protected function getSpaceType(): string
    {
        return EventManagerSpaceEnum::SENATOR;
    }

    protected function getEvents(string $type = null): array
    {
        /** @var Adherent $senator */
        $senator = $this->getUser();

        if (AbstractEventManagerController::EVENTS_TYPE_ALL === $type) {
            return $this->repository->findManagedBy([$senator->getSenatorArea()->getDepartmentTag()]);
        }

        return $this->repository->findEventsByOrganizer($senator);
    }

    protected function checkAccess(): void
    {
        $this->disableInProduction();
    }
}
