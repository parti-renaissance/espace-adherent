<?php

namespace App\Exception;

use App\Entity\Event\Event;

class EventException extends \RuntimeException
{
    private ?Event $event;

    public function __construct($message = '', ?Event $event = null, ?\Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->event = $event;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }
}
