<?php

namespace App\Chatbot\Assistant;

use App\Entity\Chatbot\Message;
use App\OpenAI\Event\ThreadEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class OpenAIAssistantHandler implements AssistantHandlerInterface
{
    public function __construct(private readonly EventDispatcherInterface $dispatcher)
    {
    }

    public function supports(Message $message): bool
    {
        return $message->thread->chatbot->isOpenAIAssistant();
    }

    public function handle(Message $message): void
    {
        $thread = $message->thread;

        $this->dispatcher->dispatch(
            new ThreadEvent(
                $thread,
                $thread->chatbot->openAiAssistant
            )
        );
    }
}
