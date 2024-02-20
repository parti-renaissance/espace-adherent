<?php

namespace App\OpenAI\Event;

use App\OpenAI\Model\AssistantInterface;
use App\OpenAI\Model\ThreadInterface;

class ThreadEvent
{
    public function __construct(
        public readonly ThreadInterface $thread,
        public readonly AssistantInterface $assistant
    ) {
    }
}
