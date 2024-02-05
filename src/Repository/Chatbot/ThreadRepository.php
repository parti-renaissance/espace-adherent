<?php

namespace App\Repository\Chatbot;

use App\Entity\Chatbot\Chatbot;
use App\Entity\Chatbot\Thread;
use App\OpenAI\Model\ThreadInterface;
use App\OpenAI\Provider\ThreadProviderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ThreadRepository extends ServiceEntityRepository implements ThreadProviderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Thread::class);
    }

    public function findOneByUuid(string $uuid): ?Thread
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

    public function findOneByIdentifier(string $identifier): ?ThreadInterface
    {
        return $this->findOneByUuid($identifier);
    }

    public function refresh(ThreadInterface $thread): void
    {
        $this->_em->refresh($thread);
    }

    public function save(ThreadInterface $thread): void
    {
        $this->_em->persist($thread);
        $this->_em->flush($thread);
    }
}
