<?php

namespace App\Event;

use App\Entity\Event\BaseEvent;
use App\Entity\Event\CauseEvent;
use App\Entity\Event\CoalitionEvent;
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
        CoalitionEvent::class => Events::EVENT_CANCELLED,
        CauseEvent::class => Events::EVENT_CANCELLED,
    ];

    public function __construct(EventDispatcherInterface $dispatcher, ObjectManager $manager)
    {
        $this->dispatcher = $dispatcher;
        $this->manager = $manager;
    }

    public function handle(BaseEvent $event): void
    {
        $event->cancel();

        $this->manager->flush();

        if (\array_key_exists($className = \get_class($event), self::EVENTS_MAPPING)) {
            $this->dispatcher->dispatch(
                $this->createDispatchedEvent($event),
                self::EVENTS_MAPPING[$className],
            );
        }
    }

    private function createDispatchedEvent(BaseEvent $event): Event
    {
        return $event instanceof CommitteeEvent
            ? new CommitteeEventEvent($event->getOrganizer(), $event, $event->getCommittee())
            : new EventEvent($event->getOrganizer(), $event)
        ;
    }
}
