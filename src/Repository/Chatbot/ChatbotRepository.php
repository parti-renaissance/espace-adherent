<?php

declare(strict_types=1);

namespace App\Repository\Chatbot;

use App\Entity\Chatbot\Chatbot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ChatbotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chatbot::class);
    }

    public function findOneEnabledBySecret(string $telegramBotSecret): ?Chatbot
    {
        return $this->createEnabledQueryBuilder('chatbot')
            ->andWhere('chatbot.telegramBotSecret = :telegram_bot_secret')
            ->setParameter('telegram_bot_secret', $telegramBotSecret)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    private function createEnabledQueryBuilder(string $alias): QueryBuilder
    {
        return $this
            ->createQueryBuilder($alias)
            ->where("$alias.enabled = :enabled")
            ->setParameter('enabled', true)
        ;
    }
}
