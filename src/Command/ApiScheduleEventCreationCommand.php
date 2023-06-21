<?php

namespace App\Command;

use App\Entity\Event\CommitteeEvent;
use App\Entity\SynchronizedEntity;
use App\Event\CommitteeEventEvent;
use App\Events;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'app:sync:events',
    description: 'Schedule Events for synchronization with api'
)]
class ApiScheduleEventCreationCommand extends AbstractApiScheduleEntityCreationCommand
{
    protected function getEntityClassname(): string
    {
        return CommitteeEvent::class;
    }

    protected function scheduleCreation(SynchronizedEntity $entity): void
    {
        $this->dispatcher->dispatch(new CommitteeEventEvent($entity->getAuthor(), $entity), Events::EVENT_CREATED);
    }
}
