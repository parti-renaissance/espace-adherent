<?php

namespace App\OpenAI\Event;

use App\OpenAI\Model\MessageInterface;

class AssistantMessageEvent
{
    public function __construct(public readonly MessageInterface $message)
    {
    }
}
