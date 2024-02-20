<?php

namespace App\Chatbot\EventListener;

use App\Chatbot\Event\AssistantMessageEvent;
use App\Telegram\Event\BotMessageEvent;
use App\Telegram\Message;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsEventListener]
class TelegramAssistantMessageListener
{
    public function __construct(private readonly EventDispatcherInterface $dispatcher)
    {
    }

    public function __invoke(AssistantMessageEvent $event): void
    {
        $message = $event->message;
        $thread = $message->thread;
        $chatbot = $thread->chatbot;

        if (!$chatbot->isTelegramBot()) {
            return;
        }

        $this->dispatcher->dispatch(
            new BotMessageEvent(
                $chatbot->telegramBot,
                new Message(
                    $thread->telegramChatId,
                    $message->text,
                    $message->entities,
                    $message->date
                )
            )
        );
    }
}
