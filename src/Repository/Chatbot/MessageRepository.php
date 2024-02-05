<?php

namespace App\Repository\Chatbot;

use App\Entity\Chatbot\Message;
use App\OpenAI\Model\MessageInterface;
use App\OpenAI\Provider\MessageProviderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MessageRepository extends ServiceEntityRepository implements MessageProviderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function save(MessageInterface $message): void
    {
        $this->_em->persist($message);
        $this->_em->flush();
    }
}
