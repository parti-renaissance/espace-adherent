<?php

declare(strict_types=1);

namespace App\Chatbot\Telegram;

use App\Entity\Chatbot\Message;

class MessageHandler
{
    public function __construct(private readonly Client $client)
    {
    }

    public function sendMessage(Message $message): void
    {
        $thread = $message->thread;
        $chatbot = $thread->chatbot;

        if (
            !$chatbot->enabled
            || !$chatbot->telegramBotApiToken
            || !$thread->telegramChatId
        ) {
            return;
        }

        $this
            ->client
            ->sendMessage(
                $chatbot->telegramBotApiToken,
                $thread->telegramChatId,
                $message->content
            )
        ;
    }
}
