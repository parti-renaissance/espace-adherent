<?php

namespace App\Chatbot\Event;

use App\Entity\Chatbot\Message;
use Symfony\Contracts\EventDispatcher\Event;

class AbstractMessageEvent extends Event
{
    public function __construct(public readonly Message $message)
    {
    }
}
