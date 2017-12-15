<?php

namespace AppBundle\CitizenProject;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;

class CitizenProjectFollowerAddedEvent extends CitizenProjectEvent
{
    private $newFollower;

    public function __construct(CitizenProject $citizenProject, Adherent $newFollower)
    {
        parent::__construct($citizenProject);

        $this->newFollower = $newFollower;
    }

    public function getNewFollower(): Adherent
    {
        return $this->newFollower;
    }
}
