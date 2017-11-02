<?php

namespace AppBundle\MoocEvent;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Group;
use AppBundle\Entity\MoocEvent;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractMoocEventEvent extends Event
{
    private $author;
    private $moocEvent;
    private $group;

    public function __construct(Adherent $author, MoocEvent $moocEvent, Group $group = null)
    {
        $this->author = $author;
        $this->moocEvent = $moocEvent;
        $this->group = $group;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getEvent(): MoocEvent
    {
        return $this->moocEvent;
    }

    public function getGroup(): ?Group
    {
        return $this->group;
    }
}
