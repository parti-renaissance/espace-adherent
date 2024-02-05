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
        return $this->createMessage($thread, MessageRoleEnum::USER, $text, $entities, $date);
    }

    private function createThread(Chatbot $chatbot): Thread
    {
        $thread = new Thread();
        $thread->chatbot = $chatbot;

        return $thread;
    }

    private function createMessage(
        Thread $thread,
        MessageRoleEnum $role,
        string $text,
        array $entities,
        \DateTimeInterface $date,
    ): Message {
        $message = new Message();
        $message->thread = $thread;
        $message->role = $role;
        $message->text = $text;
        $message->entities = $entities;
        $message->date = $date;

        return $message;
    }
}
