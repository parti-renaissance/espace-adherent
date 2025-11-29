<?php

declare(strict_types=1);

namespace App\Chatbot\Telegram;

use App\Chatbot\Command\RefreshThreadCommand;
use App\Entity\Chatbot\Chatbot;
use App\Entity\Chatbot\Thread;
use App\Repository\Chatbot\ThreadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ConversationManager
{
    public function __construct(
        private readonly ThreadRepository $threadRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function addMessage(Chatbot $chatbot, string $telegramChatId, string $content): void
    {
        $thread = $this->getCurrentThread($chatbot, $telegramChatId);

        $thread->addUserMessage($content, new \DateTimeImmutable());

        $this->entityManager->flush();

        $this->bus->dispatch(new RefreshThreadCommand($thread->getUuid()));
    }

    private function getCurrentThread(Chatbot $chatbot, string $telegramChatId): Thread
    {
        if ($thread = $this->threadRepository->findOneForTelegram($chatbot, $telegramChatId)) {
            return $thread;
        }

        $thread = $this->createThread($chatbot, $telegramChatId);

        $this->entityManager->persist($thread);
        $this->entityManager->flush();

        return $thread;
    }

    private function createThread(Chatbot $chatbot, string $telegramChatId): Thread
    {
        $thread = new Thread();
        $thread->chatbot = $chatbot;
        $thread->telegramChatId = $telegramChatId;

        return $thread;
    }
}
