<?php

namespace AppBundle\Event;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenInitiative;
use Symfony\Component\EventDispatcher\Event;

class EventCreatedCitizenInitiative extends Event
{
    private $author;
    private $initiative;

    public function __construct(Adherent $author, CitizenInitiative $initiative)
    {
        $this->author = $author;
        $this->initiative = $initiative;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getCitizenInitiative(): CitizenInitiative
    {
        return $this->initiative;
    }
}
