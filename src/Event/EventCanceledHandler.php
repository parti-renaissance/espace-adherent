<?php

namespace App\Event;

use App\Entity\Event\Event;
use App\Events;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventCanceledHandler
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly EntityManagerInterface $manager,
    ) {
    }

    public function handle(Event $event): void
    {
        $event->cancel();

        $this->manager->flush();

        $this->dispatcher->dispatch(new EventEvent($event->getOrganizer(), $event), Events::EVENT_CANCELLED);
    }
}
