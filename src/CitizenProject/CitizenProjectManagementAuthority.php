<?php

namespace AppBundle\CitizenProject;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CitizenProjectManagementAuthority
{
    private $manager;
    private $eventDispatcher;

    public function __construct(
        CitizenProjectManager $manager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->manager = $manager;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function approve(CitizenProject $citizenProject): void
    {
        $this->manager->approveCitizenProject($citizenProject);
        $this->eventDispatcher->dispatch(Events::CITIZEN_PROJECT_APPROVED, new CitizenProjectWasApprovedEvent($citizenProject));
    }

    public function refuse(CitizenProject $citizenProject): void
    {
        $this->manager->refuseCitizenProject($citizenProject);
    }

    public function preRefuse(CitizenProject $citizenProject): void
    {
        $this->manager->preRefuseCitizenProject($citizenProject);
    }

    public function preApprove(CitizenProject $project): void
    {
        $this->manager->preApproveCitizenProject($project);
    }

    public function followCitizenProject(Adherent $adherent, CitizenProject $citizenProject): void
    {
        $this->manager->followCitizenProject($adherent, $citizenProject);

        $this->eventDispatcher->dispatch(
            Events::CITIZEN_PROJECT_FOLLOWER_ADDED,
            new CitizenProjectFollowerAddedEvent($citizenProject, $adherent)
        );
    }
}
