<?php

namespace App\Chatbot;

use App\Entity\Adherent;
use App\Entity\Chatbot\Chatbot;
use App\Entity\Chatbot\Message;
use App\Entity\Chatbot\Run;
use App\Entity\Chatbot\Thread;

class ChatbotFactory
{
    public function createThread(Chatbot $chatbot, ?Adherent $adherent = null): Thread
    {
        $thread = new Thread();
        $thread->chatbot = $chatbot;
        $thread->adherent = $adherent;

        return $thread;
    }

    public function createUserMessage(Thread $thread, string $content): Message
    {
        return $this->createMessage($thread, Message::ROLE_USER, $content);
    }

    public function createAssistantMessage(Thread $thread, string $content): Message
    {
        return $this->createMessage($thread, Message::ROLE_ASSISTANT, $content);
    }

    private function createMessage(Thread $thread, string $role, string $content): Message
    {
        $message = new Message();
        $message->thread = $thread;
        $message->role = $role;
        $message->content = $content;

        return $message;
    }

    public function createRun(Thread $thread): Run
    {
        $run = new Run();
        $run->thread = $thread;

        return $run;
    }
}
