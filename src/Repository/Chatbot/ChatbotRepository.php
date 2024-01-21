<?php

namespace App\Repository\Chatbot;

use App\Entity\Chatbot\Chatbot;
use App\Entity\TelegramBot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ChatbotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chatbot::class);
    }

    public function findOneByTelegramBot(TelegramBot $telegramBot): ?Chatbot
    {
        return $this
            ->createQueryBuilder('chatbot')
            ->andWhere('chatbot.telegramBot = :telegram_bot')
            ->setParameter('telegram_bot', $telegramBot)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
