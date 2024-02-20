<?php

namespace App\Chatbot\Assistant;

use App\Entity\Chatbot\Message;

interface AssistantHandlerInterface
{
    public function supports(Message $message): bool;

    public function handle(Message $message): void;
}
