<?php

namespace App\CitizenProject;

use App\Entity\CitizenProject;
use App\Events;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CitizenProjectManagementAuthority
{
    private $manager;
    private $eventDispatcher;

    public function __construct(CitizenProjectManager $manager, EventDispatcherInterface $eventDispatcher)
    {
        $this->manager = $manager;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function approve(CitizenProject $citizenProject): void
    {
        $this->manager->approveCitizenProject($citizenProject);
        $this->eventDispatcher->dispatch(new CitizenProjectWasApprovedEvent($citizenProject), Events::CITIZEN_PROJECT_APPROVED);
        $this->dispatchUpdate($citizenProject);
    }

    public function refuse(CitizenProject $citizenProject): void
    {
        $this->manager->refuseCitizenProject($citizenProject);
        $this->dispatchUpdate($citizenProject);
    }

    public function preRefuse(CitizenProject $citizenProject): void
    {
        $this->manager->preRefuseCitizenProject($citizenProject);
        $this->dispatchUpdate($citizenProject);
    }

    public function preApprove(CitizenProject $project): void
    {
        $this->manager->preApproveCitizenProject($project);
        $this->dispatchUpdate($project);
    }

    private function dispatchUpdate(CitizenProject $citizenProject): void
    {
        $this->eventDispatcher->dispatch(new CitizenProjectWasUpdatedEvent($citizenProject), Events::CITIZEN_PROJECT_UPDATED);
    }
}
