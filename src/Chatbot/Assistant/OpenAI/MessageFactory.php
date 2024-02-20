<?php

namespace App\Chatbot\Assistant\OpenAI;

use App\Chatbot\ThreadFactory;
use App\Entity\Chatbot\Run;
use App\Entity\Chatbot\Thread;
use App\Entity\OpenAI\Assistant;
use App\OpenAI\MessageFactoryInterface;
use App\OpenAI\Model\AssistantInterface;
use App\OpenAI\Model\MessageInterface;
use App\OpenAI\Model\RunInterface;
use App\OpenAI\Model\ThreadInterface;

class MessageFactory implements MessageFactoryInterface
{
    public function __construct(private readonly ThreadFactory $threadFactory)
    {
    }

    public function createAssistantMessage(
        ThreadInterface $thread,
        string $openAiId,
        string $text,
        array $annotations,
        \DateTimeInterface $date,
        ?AssistantInterface $assistant,
        ?RunInterface $run
    ): MessageInterface {
        if (!$thread instanceof Thread) {
            throw new \InvalidArgumentException('This factory can only handle "%s" entities.', Thread::class);
        }

        $message = $this->threadFactory->createAssistantMessage($thread, $text, $annotations, $date);
        $message->openAiId = $openAiId;

        if ($assistant instanceof Assistant) {
            $message->assistant = $assistant;
        }

        if ($run instanceof Run) {
            $message->run = $run;
        }

        return $message;
    }
}
