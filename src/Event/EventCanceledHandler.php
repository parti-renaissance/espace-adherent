<?php

namespace App\Event;

use App\CitizenAction\CitizenActionEvent;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\CitizenAction;
use App\Entity\Event\CommitteeEvent;
use App\Events;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventCanceledHandler
{
    private $dispatcher;
    private $manager;

    private const EVENTS_MAPPING = [
        CommitteeEvent::class => Events::EVENT_CANCELLED,
        CitizenAction::class => Events::CITIZEN_ACTION_CANCELLED,
    ];

    public function __construct(EventDispatcherInterface $dispatcher, ObjectManager $manager)
    {
        $this->dispatcher = $dispatcher;
        $this->manager = $manager;
    }

    public function handle(BaseEvent $event): BaseEvent
    {
        $event->cancel();

        $this->manager->flush();

        if (\array_key_exists($className = \get_class($event), self::EVENTS_MAPPING)) {
            $this->dispatcher->dispatch(
                $this->createDispatchedEvent($event),
                self::EVENTS_MAPPING[$className],
            );
        }

        return $event;
    }

    private function createDispatchedEvent(BaseEvent $event): Event
    {
        if ($event instanceof CommitteeEvent) {
            return new CommitteeEventEvent(
                $event->getOrganizer(),
                $event,
                $event->getCommittee()
            );
        }

        return new CitizenActionEvent($event, $event->getOrganizer());
    }
}
