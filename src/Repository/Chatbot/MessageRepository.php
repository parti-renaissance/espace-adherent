<?php

declare(strict_types=1);

namespace App\Repository\Chatbot;

use App\Entity\Chatbot\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function findOneByUuid(Uuid|string $uuid): ?Message
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }
}
