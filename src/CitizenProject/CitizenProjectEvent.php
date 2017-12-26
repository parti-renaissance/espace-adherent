<?php

namespace AppBundle\CitizenProject;

use AppBundle\Entity\CitizenProject;
use Symfony\Component\EventDispatcher\Event;

class CitizenProjectEvent extends Event
{
    private $citizenProject;

    public function __construct(CitizenProject $citizenProject = null)
    {
        $this->citizenProject = $citizenProject;
    }

    public function getCitizenProject()
    {
        return $this->citizenProject;
    }
}
