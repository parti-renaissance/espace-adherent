<?php

namespace App\Chatbot;

use App\Chatbot\Enum\MessageRoleEnum;
use App\Entity\Chatbot\Chatbot;
use App\Entity\Chatbot\Message;
use App\Entity\Chatbot\Thread;

class ThreadFactory
{
    public function createTelegramThread(Chatbot $chatbot, string $telegramChatId): Thread
    {
        $thread = $this->createThread($chatbot);
        $thread->telegramChatId = $telegramChatId;

        return $thread;
    }

    public function createUserMessage(
        Thread $thread,
        string $text,
        array $entities,
        \DateTimeInterface $date
    ): Message {
        return Message::create($thread, MessageRoleEnum::USER, $text, $entities, $date);
    }

    public function createAssistantMessage(
        Thread $thread,
        string $text,
        array $entities,
        \DateTimeInterface $date
    ): Message {
        return Message::create($thread, MessageRoleEnum::ASSISTANT, $text, $entities, $date);
    }

    protected function createThread(Chatbot $chatbot): Thread
    {
        $thread = new Thread();
        $thread->chatbot = $chatbot;

        return $thread;
    }
}
