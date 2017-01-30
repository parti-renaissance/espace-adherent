<?php

namespace AppBundle\Committee\Event;

use AppBundle\Committee\CommitteeEvent as BaseEvent;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeEvent;

class CommitteeEventCreatedEvent extends BaseEvent
{
    private $author;
    private $event;

    public function __construct(Committee $committee, Adherent $author, CommitteeEvent $event)
    {
        parent::__construct($committee);

        $this->author = $author;
        $this->event = $event;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getCommitteeEvent(): CommitteeEvent
    {
        return $this->event;
    }
}
