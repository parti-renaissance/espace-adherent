<?php

namespace App\Command;

use App\Committee\CommitteeEvent;
use App\Entity\Committee;
use App\Entity\SynchronizedEntity;
use App\Events;

class ApiScheduleCommitteeCreationCommand extends ApiScheduleEntityCreationCommand
{
    protected function configure()
    {
        $this
            ->setName('app:sync:committees')
            ->setDescription('Schedule Committees for synchronization with api')
        ;
    }

    protected function getEntityClassname(): string
    {
        return Committee::class;
    }

    protected function scheduleCreation(SynchronizedEntity $committee): void
    {
        $this->dispatcher->dispatch(Events::COMMITTEE_CREATED, new CommitteeEvent($committee));
    }
}
