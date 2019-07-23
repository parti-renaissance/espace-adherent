<?php

namespace AppBundle\Controller\EnMarche\EventManager;

use AppBundle\Entity\Event;
use AppBundle\Event\EventCommand;
use AppBundle\Event\EventCommandHandler;
use AppBundle\Event\EventInterface;
use AppBundle\Event\EventManagerSpaceEnum;
use AppBundle\Event\EventRegistrationCommand;
use AppBundle\Event\EventRegistrationCommandHandler;
use AppBundle\Repository\EventRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-referent", name="app_referent_event_manager_")
 *
 * @Security("is_granted('ROLE_REFERENT')")
 */
class EventManagerReferent extends AbstractEventManagerController
{
    private $repository;
    private $eventCommandHandler;
    private $eventRegistrationCommandHandler;

    public function __construct(
        EventRepository $repository,
        EventCommandHandler $eventCommandHandler,
        EventRegistrationCommandHandler $eventRegistrationCommandHandler
    ) {
        $this->repository = $repository;
        $this->eventCommandHandler = $eventCommandHandler;
        $this->eventRegistrationCommandHandler = $eventRegistrationCommandHandler;
    }

    protected function getSpaceType(): string
    {
        return EventManagerSpaceEnum::REFERENT;
    }

    protected function getEvents(string $type = null): array
    {
        if (AbstractEventManagerController::EVENTS_TYPE_ALL === $type) {
            return $this->repository->findManagedBy($this->getUser());
        }

        return $this->repository->findEventsByOrganizer($this->getUser());
    }

    protected function getEventClassName(): string
    {
        return Event::class;
    }

    protected function handleCreationCommand(EventCommand $command): EventInterface
    {
        $event = $this->eventCommandHandler->handle($command, $this->getEventClassName());

        $this->eventRegistrationCommandHandler->handle(
            new EventRegistrationCommand($event, $this->getUser())
        );

        return $event;
    }
}
