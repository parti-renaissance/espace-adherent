<?php

namespace AppBundle\Committee\Feed;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event as EntityEvent;

class CommitteeEvent
{
    private $event;
    private $author;

    public function __construct(Adherent $author, EntityEvent $event)
    {
        $this->author = $author;
        $this->event = $event;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->event->getCreatedAt();
    }

    public function getAuthor(): Adherent
    {
        return $this->author;
    }

    public function getCommittee(): Committee
    {
        return $this->event->getCommittee();
    }

    public function getEvent(): EntityEvent
    {
        return $this->event;
    }
}
