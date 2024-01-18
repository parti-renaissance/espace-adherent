<?php

namespace App\Repository\Chatbot;

use App\Entity\Chatbot\Thread;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ThreadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Thread::class);
    }

    public function findOneByUuid(string $uuid): ?Thread
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    public function refresh(Thread $thread): void
    {
        $this->_em->refresh($thread);
    }
}
