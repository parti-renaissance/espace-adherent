<?php

namespace App\Chatbot;

use App\Chatbot\Command\RefreshThreadCommand;
use App\Entity\Adherent;
use App\Entity\Chatbot\Chatbot;
use App\Entity\Chatbot\Thread;
use App\Repository\Chatbot\ThreadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ConversationManager
{
    public function __construct(
        private readonly ThreadRepository $threadRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
        private readonly SessionInterface $session
    ) {
    }

    public function getCurrentThread(Chatbot $chatbot, ?Adherent $adherent): Thread
    {
        $threadKey = self::buildThreadKey($chatbot->code);

        if ($this->session->has($threadKey)) {
            $thread = $this->threadRepository->findOneByUuid($this->session->get($threadKey));

            if ($thread) {
                return $thread;
            }

            $this->session->remove($threadKey);
        }

        $thread = $this->createThread($chatbot, $adherent);

        $this->entityManager->persist($thread);
        $this->entityManager->flush();

        $this->session->set($threadKey, $thread->getUuid());

        return $thread;
    }

    public function addMessage(Thread $thread, string $content): void
    {
        $thread->addUserMessage($content, new \DateTimeImmutable());

        $this->entityManager->flush();

        $this->bus->dispatch(new RefreshThreadCommand($thread->getUuid()));
    }

    public function end(Chatbot $chatbot): void
    {
        $threadKey = self::buildThreadKey($chatbot->code);

        $this->session->remove($threadKey);
    }

    private function createThread(Chatbot $chatbot, ?Adherent $adherent): Thread
    {
        $thread = new Thread();
        $thread->chatbot = $chatbot;
        $thread->adherent = $adherent;

        return $thread;
    }

    private static function buildThreadKey(string $chatbotCode): string
    {
        return "chatbot-$chatbotCode-thread";
    }
}
