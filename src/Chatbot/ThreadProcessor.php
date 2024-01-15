<?php

namespace App\Chatbot;

use App\Entity\Chatbot\Message;
use App\Entity\Chatbot\Run;
use App\Entity\Chatbot\Thread;
use Doctrine\ORM\EntityManagerInterface;

class ThreadProcessor
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Client $client
    ) {
    }

    public function process(Thread $thread): void
    {
        $this->initializeThread($thread);

        $newMessages = $thread->getMessagesToInitialize();

        if (!$newMessages->isEmpty()) {
            foreach ($newMessages as $newMessage) {
                $this->initializeMessage($newMessage);
            }

            dump("has new messages");
            $this->startCurrentRun($thread);
        } elseif ($currentRun = $thread->currentRun) {
            if ($currentRun->needRefresh()) {
                $this->refreshRunStatus($currentRun);
            }

            if (!$currentRun->needRefresh()) {
                $this->endCurrentRun($thread);

                $this->retrieveLastMessages($thread);
            }
        }
    }

    private function initializeThread(Thread $thread): void
    {
        if ($thread->isInitialized()) {
            return;
        }

        $thread->externalId = $this->client->createThread();

        $this->entityManager->flush();
    }

    private function initializeMessage(Message $message): void
    {
        if ($message->isInitialized()) {
            return;
        }

        $message->externalId = $this->client->createMessage($message);

        $this->entityManager->flush();
    }

    private function initializeRun(Run $run): void
    {
        if ($run->isInitialized()) {
            return;
        }

        $run->externalId = $this->client->createRun($run);

        $this->entityManager->flush();
    }

    private function cancelRun(Run $run): void
    {
        if ($run->isInitialized() && $run->isInProgress()) {
            $this->client->cancelRun($run);
        }

        $run->cancel();

        $this->entityManager->flush();
    }

    private function refreshRunStatus(Run $run): void
    {
        $run->status = $this->client->getRunStatus($run);

        $this->entityManager->flush();
    }

    private function startCurrentRun(Thread $thread): void
    {
        if ($thread->currentRun) {
            $this->cancelRun($thread->currentRun);
        }

        $thread->startNewRun();

        $this->entityManager->flush();

        $this->initializeRun($thread->currentRun);
    }

    private function endCurrentRun(Thread $thread): void
    {
        $thread->endCurrentRun();

        $this->entityManager->flush();
    }

    private function retrieveLastMessages(Thread $thread): void
    {
        $lastMessages = $this->client->getLastMessages($thread);

        foreach ($lastMessages as $message) {
            if ($message->isUserMessage()) {
                continue;
            }

            if ($thread->hasMessageWithExternalId($message->id)) {
                continue;
            }

            $thread->addAssistantMessage($message->content, $message->createdAt);
        }

        $this->entityManager->flush();
    }
}
