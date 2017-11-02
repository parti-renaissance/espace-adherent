<?php

namespace AppBundle\MoocEvent;

use AppBundle\Entity\MoocEvent;
use Symfony\Component\EventDispatcher\Event;

class MoocEventValidatedEvent extends Event
{
    private $moocEvent;

    public function __construct(MoocEvent $moocEvent)
    {
        $this->moocEvent = $moocEvent;
    }

    public function getMoocEvent(): MoocEvent
    {
        return $this->moocEvent;
    }
}
