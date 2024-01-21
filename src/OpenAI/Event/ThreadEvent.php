<?php

namespace App\OpenAI\Event;

use App\Entity\Chatbot\Thread;
use App\OpenAI\AssistantInterface;

class ThreadEvent
{
    public function __construct(
        public readonly Thread $thread,
        public readonly AssistantInterface $assistant
    ) {
    }
}
