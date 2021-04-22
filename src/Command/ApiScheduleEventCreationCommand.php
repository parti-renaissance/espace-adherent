<?php

namespace App\Command;

use App\Entity\Event\CommitteeEvent;
use App\Entity\SynchronizedEntity;
use App\Event\CommitteeEventEvent;
use App\Events;

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
        return CommitteeEvent::class;
    }

    protected function scheduleCreation(SynchronizedEntity $event): void
    {
        $this->dispatcher->dispatch(new CommitteeEventEvent(null, $event), Events::EVENT_CREATED);
    }
}
