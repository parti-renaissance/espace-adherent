<?php

namespace AppBundle\Command;

use AppBundle\Committee\CommitteeEvent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\SynchronizedEntity;
use AppBundle\Events;

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
