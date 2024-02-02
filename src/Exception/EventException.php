<?php

namespace App\Exception;

use App\Entity\Event\CommitteeEvent;

class EventException extends \RuntimeException
{
    private $event;

    public function __construct($message = '', ?CommitteeEvent $event = null, ?\Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->event = $event;
    }

    public function getEvent(): ?CommitteeEvent
    {
        return $this->event;
    }
}
