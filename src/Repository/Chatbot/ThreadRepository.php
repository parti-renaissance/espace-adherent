<?php

declare(strict_types=1);

namespace App\Repository\Chatbot;

use App\Entity\Chatbot\Thread;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

class ThreadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Thread::class);
    }

    public function findOneByUuid(Uuid|string $uuid): ?Thread
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }
}
