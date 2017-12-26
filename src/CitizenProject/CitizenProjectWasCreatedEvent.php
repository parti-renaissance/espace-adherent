<?php

namespace AppBundle\CitizenProject;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;

class CitizenProjectWasCreatedEvent extends CitizenProjectEvent
{
    private $creator;

    public function __construct(CitizenProject $citizenProject, Adherent $creator)
    {
        parent::__construct($citizenProject);

        $this->creator = $creator;
    }

    public function getCreator(): Adherent
    {
        return $this->creator;
    }
}
