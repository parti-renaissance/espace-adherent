<?php

namespace App\OpenAI;

use App\Entity\Chatbot\Thread;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class Logger implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function log(Thread $thread, string $message): void
    {
        $this->logger->info(
            sprintf(
                '[OpenAI][Thread:%s] %s',
                $thread->getUuid()->toString(),
                $message
            )
        );
    }
}
