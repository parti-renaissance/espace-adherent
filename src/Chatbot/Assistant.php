<?php

namespace App\Chatbot;

use App\Chatbot\Assistant\AssistantHandlerInterface;
use App\Entity\Chatbot\Message;

class Assistant
{
    /**
     * @param AssistantHandlerInterface[]|$assistantHandlers
     */
    public function __construct(private readonly iterable $assistantHandlers = [])
    {
    }

    public function handle(Message $message): void
    {
        foreach ($this->assistantHandlers as $handler) {
            if ($handler->supports($message)) {
                $handler->handle($message);

                return;
            }
        }
    }
}
