<?php

namespace AppBundle\Controller\EnMarche\EventManager;

use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Entity\Adherent;
use AppBundle\Event\EventManagerSpaceEnum;
use AppBundle\Repository\EventRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-depute", name="app_deputy_event_manager_")
 *
 * @Security("is_granted('ROLE_DEPUTY')")
 */
class DeputyEventManagerController extends AbstractEventManagerController
{
    use CanaryControllerTrait;

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

    protected function checkAccess(): void
    {
        $this->disableInProduction();
    }
}
