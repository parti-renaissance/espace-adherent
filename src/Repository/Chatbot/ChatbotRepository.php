<?php

namespace App\Repository\Chatbot;

use App\Entity\Chatbot\Chatbot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ChatbotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chatbot::class);
    }

    public function findOneByCode(string $code): ?Chatbot
    {
        return $this->createQueryBuilder('chatbot')
            ->where('chatbot.code = :code')
            ->andWhere('chatbot.enabled = :enabled')
            ->setParameter('code', $code)
            ->setParameter('enabled', true)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
