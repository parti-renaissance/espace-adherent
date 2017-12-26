<?php

namespace AppBundle\CitizenInitiative;

use AppBundle\Entity\CitizenInitiative;
use Symfony\Component\EventDispatcher\Event;

class CitizenInitiativeValidatedEvent extends Event
{
    private $initiative;

    public function __construct(CitizenInitiative $initiative)
    {
        $this->initiative = $initiative;
    }

    public function getCitizenInitiative(): CitizenInitiative
    {
        return $this->initiative;
    }
}
