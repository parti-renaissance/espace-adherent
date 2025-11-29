<?php

namespace App\Repository\Chatbot;

use App\Entity\Chatbot\Chatbot;
use App\Entity\Chatbot\Thread;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;

class ThreadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Thread::class);
    }

    public function findOneByUuid(UuidInterface|string $uuid): ?Thread
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    public function findOneForTelegram(Chatbot $chatbot, string $telegramChatId): ?Thread
    {
        return $this
            ->createQueryBuilder('thread')
            ->where('thread.chatbot = :chatbot')
            ->andWhere('thread.telegramChatId = :telegram_chat_id')
            ->setParameter('chatbot', $chatbot)
            ->setParameter('telegram_chat_id', $telegramChatId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function refresh(Thread $thread): void
    {
        $this->_em->refresh($thread);
    }
}
