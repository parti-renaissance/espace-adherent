<?php

namespace App\Chatbot;

use App\Entity\Chatbot\Run;
use App\Entity\Chatbot\Thread;
use App\OpenAI\Client;

class ThreadProcessor
{
    public function __construct(private readonly Client $openAi)
    {
    }

    public function process(Thread $thread): void
    {
        if (!$thread->externalId) {
            $threadExternalId = $this->openAi->createThread();

            $thread->externalId = $threadExternalId;
        }

        foreach ($thread->messages as $message) {
            if (!$message->isUserMessage()) {
                continue;
            }

            if (!$message->externalId) {
                $messageExternalId = $this->openAi->addUserMessage($thread->externalId, $message->content);

                $message->externalId = $messageExternalId;
            }
        }

        if ($currentRun = $thread->currentRun) {
            if (!$currentRun->externalId) {
                $currentRunExternalId =  $this->openAi->createRun($thread->externalId, $thread->chatbot->assistantId);

                $currentRun->externalId = $currentRunExternalId;
            }


        }
    }
}
