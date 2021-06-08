<?php

namespace App\Event;

use App\Entity\Event\BaseEvent;
use App\Entity\Event\CommitteeEvent;
use App\Events;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventCanceledHandler
{
    private $dispatcher;
    private $manager;

    public function __construct(EventDispatcherInterface $dispatcher, ObjectManager $manager)
    {
        $this->dispatcher = $dispatcher;
        $this->manager = $manager;
    }

    public function handle(BaseEvent $event): void
    {
        $event->cancel();

        $this->manager->flush();

        if ($event->needNotifyForCancellation()) {
            $this->dispatcher->dispatch(
                $this->createDispatchedEvent($event),
                Events::EVENT_CANCELLED,
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
