<?php

namespace AppBundle\Event;

use AppBundle\Committee\CommitteeEvent as BaseEvent;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;

class EventCancelledEvent extends BaseEvent
{
    private $author;
    private $event;

    public function __construct(Adherent $author, Event $event, Committee $committee = null)
    {
        parent::__construct($committee);

        $this->author = $author;
        $this->event = $event;
    }

    public function getAuthor(): Adherent
    {
        return $this->author;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }
}
