<?php

namespace AppBundle\ApplicationRequest;

use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use AppBundle\Entity\ApplicationRequest\VolunteerRequest;
use Doctrine\ORM\EntityManagerInterface;

class ApplicationRequestHandler
{
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function handleVolunteerRequest(VolunteerRequest $volunteerRequest): void
    {
        $this->manager->persist($volunteerRequest);
        $this->manager->flush();

        // TODO: send email to confirm volunteer request
    }

    public function handleRunningMateRequest(RunningMateRequest $runningMateRequest): void
    {
        $this->manager->persist($runningMateRequest);
        $this->manager->flush();

        // TODO: send email to confirm running mate request
    }
}
