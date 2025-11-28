<?php

declare(strict_types=1);

namespace App\Chatbot;

use App\Chatbot\Command\SendTelegramMessageCommand;
use App\Entity\Chatbot\Message;
use App\Entity\Chatbot\Run;
use App\Entity\Chatbot\Thread;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ThreadProcessor
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Client $client,
        private readonly MessageBusInterface $bus,
        private readonly Logger $logger,
    ) {
    }

    public function process(Thread $thread): void
    {
        $this->logger->logThread($thread, 'Starting process');

        $this->initializeThread($thread);

        $newMessages = $thread->getMessagesToInitialize();

        if (!$newMessages->isEmpty()) {
            $this->logger->logThread($thread, \sprintf('Found %d messages to initialize', $newMessages->count()));

            foreach ($newMessages as $newMessage) {
                $this->initializeMessage($newMessage);
            }

            $this->startNewRun($thread);
        } elseif ($currentRun = $thread->currentRun) {
            $this->logger->logThread($thread, \sprintf('Processing current Run (uuid: "%s")', $currentRun->getUuid()->toString()));

            if ($currentRun->needRefresh()) {
                $this->refreshRunStatus($currentRun);
            }

            if (!$currentRun->needRefresh()) {
                $this->endCurrentRun($thread);

                $this->retrieveLastMessages($thread);
            }
        } else {
            $this->logger->logThread($thread, 'No new message and no current run to process');
        }

        $this->logger->logThread($thread, 'Ending process');
    }

    private function initializeThread(Thread $thread): void
    {
        if ($thread->isInitialized()) {
            return;
        }

        $this->logger->logThread($thread, 'Initializing remote Thread');

        $thread->externalId = $this->client->createThread();

        $this->entityManager->flush();
    }

    private function initializeMessage(Message $message): void
    {
        if ($message->isInitialized()) {
            return;
        }

        $this->logger->logThread($message->thread, \sprintf('Initializing remote Message (uuid: "%s")', $message->getUuid()->toString()));

        $message->externalId = $this->client->createMessage($message);

        $this->entityManager->flush();
    }

    private function initializeRun(Run $run): void
    {
        if ($run->isInitialized()) {
            return;
        }

        $this->logger->logThread($run->thread, \sprintf('Initializing remote Run (uuid: "%s")', $run->getUuid()->toString()));

        $run->externalId = $this->client->createRun($run);

        $this->entityManager->flush();
    }

    private function cancelRun(Run $run): void
    {
        $this->logger->logThread($run->thread, \sprintf('Cancelling Run (uuid: "%s")', $run->getUuid()->toString()));

        if ($run->isInitialized() && $run->isInProgress()) {
            $this->logger->logThread($run->thread, \sprintf('Cancelling remote Run (uuid: "%s")', $run->getUuid()->toString()));

            $this->client->cancelRun($run);
        }

        $run->cancel();

        $this->entityManager->flush();
    }

    private function refreshRunStatus(Run $run): void
    {
        $this->logger->logThread($run->thread, \sprintf('Refreshing Run status (uuid: "%s")', $run->getUuid()->toString()));

        $run->status = $this->client->getRunStatus($run);

        $this->entityManager->flush();
    }

    private function startNewRun(Thread $thread): void
    {
        $this->logger->logThread($thread, 'Starting new Run');

        if ($thread->currentRun) {
            $this->cancelRun($thread->currentRun);
        }

        $thread->startNewRun();

        $this->entityManager->flush();

        $this->initializeRun($thread->currentRun);
    }

    private function endCurrentRun(Thread $thread): void
    {
        $this->logger->logThread($thread, 'Ending current Run');

        $thread->endCurrentRun();

        $this->entityManager->flush();
    }

    private function retrieveLastMessages(Thread $thread): void
    {
        $this->logger->logThread($thread, 'Retrieving last messages');

        $lastMessages = $this->client->getLastMessages($thread);

        foreach ($lastMessages as $message) {
            if ($message->isUserMessage()) {
                continue;
            }

            if ($thread->hasMessageWithExternalId($message->id)) {
                continue;
            }

            $this->logger->logThread($thread, 'Saving new assistant Message');

            $this->handleNewAssistantMessage(
                $thread,
                $message->content,
                $message->createdAt,
                $message->id
            );
        }

        $this->entityManager->flush();
    }

    private function handleNewAssistantMessage(
        Thread $thread,
        string $content,
        \DateTimeInterface $date,
        string $externalId,
    ): void {
        $message = $thread->addAssistantMessage($content, $date, $externalId);

        $this->entityManager->flush();

        if ($thread->telegramChatId) {
            $this->bus->dispatch(new SendTelegramMessageCommand($message->getUuid()));
        }
    }
}
