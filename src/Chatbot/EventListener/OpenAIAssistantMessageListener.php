<?php

namespace App\Chatbot\EventListener;

use App\Chatbot\Event\AssistantMessageEvent;
use App\Entity\Chatbot\Message;
use App\OpenAI\Event\AssistantMessageEvent as OpenAIAssistantMessageEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsEventListener]
class OpenAIAssistantMessageListener
{
    public function __construct(private readonly EventDispatcherInterface $dispatcher)
    {
    }

    public function __invoke(OpenAIAssistantMessageEvent $event): void
    {
        $message = $event->message;

        if (!$message instanceof Message) {
            return;
        }

        $this->dispatcher->dispatch(new AssistantMessageEvent($message));
    }
}
