<?php

use App\CitizenAction\CitizenActionEvent;
use App\CitizenProject\CitizenProjectEvent;
use App\CitizenProject\CitizenProjectWasCreatedEvent;
use App\CitizenProject\CitizenProjectWasUpdatedEvent;
use App\Committee\CommitteeEvent;
use App\Entity\Adherent;
use App\Entity\CitizenProject;
use App\Entity\Committee;
use App\Entity\Event\CitizenAction;
use App\Entity\Event\CommitteeEvent as CommitteeEventEntity;
use App\Event\CommitteeEventEvent;
use App\Events;
use App\Membership\UserEvent;
use Behat\Behat\Context\Context;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SyncWithAPIContext implements Context
{
    private $doctrine;
    private $dispatcher;

    public function __construct(Registry $doctrine, EventDispatcherInterface $dispatcher)
    {
        $this->doctrine = $doctrine;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @When I dispatch the :event user event with :email
     */
    public function iDispatchUserEvent(string $event, string $email)
    {
        $adherent = $this->doctrine->getRepository(Adherent::class)->findOneBy(['emailAddress' => $email]);

        $this->dispatcher->dispatch(new UserEvent($adherent, true, true), $event);
    }

    /**
     * @When I dispatch the :event user event with :email and email subscriptions
     */
    public function iDispatchUserEventWithEmailSubscriptions(string $event, string $email)
    {
        $adherent = $this->doctrine->getRepository(Adherent::class)->findOneBy(['emailAddress' => $email]);

        $subscriptions = $adherent->getSubscriptionTypes();
        if (!isset($subscriptions[0])) {
            throw new \Exception('User has no email subscription.');
        }
        unset($subscriptions[0]);

        $this->dispatcher->dispatch(new UserEvent($adherent, null, null, $subscriptions), $event);
    }

    /**
     * @When I dispatch the :event committee event with :committeeName
     */
    public function iDispatchCommitteeEvent(string $event, string $committeeName): void
    {
        /** @var Committee $committee */
        $committee = $this->doctrine->getRepository(Committee::class)->findOneBy(['name' => $committeeName]);

        $this->dispatcher->dispatch(new CommitteeEvent($committee), $event);
    }

    /**
     * @When I dispatch the :event event event with :eventName
     */
    public function iDispatchEventEvent(string $event, string $eventName): void
    {
        /** @var CommitteeEventEntity $committeeEvent */
        $committeeEvent = $this->doctrine->getRepository(CommitteeEventEntity::class)->findOneBy(['name' => $eventName]);

        $this->dispatcher->dispatch(new CommitteeEventEvent(null, $committeeEvent), $event);
    }

    /**
     * @When I dispatch the :event citizen project event with :citizenProjectName
     */
    public function iDispatchCitizenProjectEvent(string $event, string $citizenProjectName): void
    {
        /** @var CitizenProject $citizenProject */
        $citizenProject = $this->doctrine->getRepository(CitizenProject::class)->findOneBy(['name' => $citizenProjectName]);

        switch ($event) {
            case Events::CITIZEN_PROJECT_CREATED:
                $creator = $this->doctrine->getRepository(Adherent::class)->findOneByUuid($citizenProject->getCreatedBy());

                $citizenProjectEvent = new CitizenProjectWasCreatedEvent($citizenProject, $creator);

                break;
            case Events::CITIZEN_PROJECT_UPDATED:
                $citizenProjectEvent = new CitizenProjectWasUpdatedEvent($citizenProject);

                break;
            case Events::CITIZEN_PROJECT_DELETED:
                $citizenProjectEvent = new CitizenProjectEvent($citizenProject);

                break;
            default:
                throw new \Exception("The event \"$event\" is not implemented for testing.");
        }

        $this->dispatcher->dispatch($citizenProjectEvent, $event);
    }

    /**
     * @When I dispatch the :event citizen action event with :citizenActionName
     */
    public function iDispatchCitizenActionEvent(string $event, string $citizenActionName): void
    {
        /** @var CitizenAction $citizenAction */
        $citizenAction = $this->doctrine->getRepository(CitizenAction::class)->findOneBy(['name' => $citizenActionName]);

        $this->dispatcher->dispatch(new CitizenActionEvent($citizenAction), $event);
    }
}
