<?php

namespace App\Command;

use App\Committee\Event\CommitteeEvent;
use App\Entity\Committee;
use App\Entity\SynchronizedEntity;
use App\Events;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'app:sync:committees',
    description: 'Schedule Committees for synchronization with ap'
)]
class ApiScheduleCommitteeCreationCommand extends AbstractApiScheduleEntityCreationCommand
{
    protected function getEntityClassname(): string
    {
        return Committee::class;
    }

    protected function scheduleCreation(SynchronizedEntity $entity): void
    {
        $this->dispatcher->dispatch(new CommitteeEvent($entity), Events::COMMITTEE_CREATED);
    }
}
