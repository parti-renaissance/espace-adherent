<?php

namespace App\Repository\Chatbot;

use App\Chatbot\Enum\MessageRoleEnum;
use App\Entity\Chatbot\Message;
use App\OpenAI\Model\MessageInterface;
use App\OpenAI\Model\RunInterface;
use App\OpenAI\Model\ThreadInterface;
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

    public function createAssistantMessage(
        ThreadInterface $thread,
        string $openAiId,
        string $text,
        array $annotations,
        \DateTimeInterface $date,
        ?RunInterface $run
    ): MessageInterface {
        $message = new Message();
        $message->thread = $thread;
        $message->role = MessageRoleEnum::ASSISTANT;
        $message->openAiId = $openAiId;
        $message->text = $text;
        $message->entities = $annotations;
        $message->date = $date;
        $message->run = $run;

        return $message;
    }
}
