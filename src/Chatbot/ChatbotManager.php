<?php

declare(strict_types=1);

namespace App\Chatbot;

use App\Entity\Adherent;
use App\Entity\Chatbot\Thread;
use App\Repository\Chatbot\ThreadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;

class ChatbotManager
{
    private const MAX_CONTEXT_MESSAGES = 15;
    private const FIRST_MESSAGE_TITLE_LENGTH = 80;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ThreadRepository $threadRepository,
    ) {
    }

    public function handleUserMessage(string $content, ?string $threadId, Adherent $adherent): Thread
    {
        $thread = null;

        if ($threadId) {
            $thread = $this->threadRepository->findOneByUuid($threadId);
        }

        if (!$thread || $thread->adherent !== $adherent) {
            $thread = new Thread($adherent, mb_substr(trim($content), 0, self::FIRST_MESSAGE_TITLE_LENGTH) ?: null);
            $this->entityManager->persist($thread);
        }

        $thread->addUserMessage($content);

        $this->entityManager->flush();

        return $thread;
    }

    public function handleBotResponse(Thread $thread, string $content): void
    {
        if (empty($content)) {
            return;
        }

        $thread->addAssistantMessage($content, new \DateTimeImmutable());

        $this->entityManager->flush();
    }

    public function buildContextMessageBag(Thread $thread): MessageBag
    {
        $all = $thread->messages->toArray();
        $recent = \array_slice($all, -self::MAX_CONTEXT_MESSAGES);

        $first = $recent[0] ?? null;
        if ($first && !$first->isUserMessage()) {
            $recent = \array_slice($recent, 1);
        }

        $bag = new MessageBag();
        foreach ($recent as $msg) {
            $bag->add($msg->isUserMessage()
                ? Message::ofUser($msg->content)
                : Message::ofAssistant($msg->content));
        }

        return $bag;
    }
}
