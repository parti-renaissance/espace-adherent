<?php

namespace AppBundle\Command;

use AppBundle\Entity\Event;
use AppBundle\Entity\SynchronizedEntity;
use AppBundle\Event\EventEvent;
use AppBundle\Events;

class ApiScheduleEventCreationCommand extends ApiScheduleEntityCreationCommand
{
    protected function configure()
    {
        $this
            ->setName('app:sync:events')
            ->setDescription('Schedule Events for synchronization with api')
        ;
    }

    protected function getEntityClassname(): string
    {
        return Event::class;
    }

    protected function scheduleCreation(SynchronizedEntity $event): void
    {
        $this->dispatcher->dispatch(Events::EVENT_CREATED, new EventEvent(null, $event));
    }
}
