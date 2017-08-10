<?php

namespace AppBundle\CitizenInitiative;

use AppBundle\Committee\CommitteeEvent as BaseEvent;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenInitiative;

class CitizenInitiativeUpdatedEvent extends BaseEvent
{
    private $author;
    private $initiative;

    public function __construct(Adherent $author, CitizenInitiative $initiative)
    {
        $this->author = $author;
        $this->initiative = $initiative;
    }

    public function getAuthor(): Adherent
    {
        return $this->author;
    }

    public function getCitizenInitiative(): CitizenInitiative
    {
        return $this->initiative;
    }
}
