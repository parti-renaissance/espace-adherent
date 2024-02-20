<?php

namespace App\OpenAI;

use App\OpenAI\Model\ThreadInterface;
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

    public function log(ThreadInterface $thread, string $message): void
    {
        $this->logger->info(
            sprintf(
                '[OpenAI][Thread:%s] %s',
                $thread->getIdentifier(),
                $message
            )
        );
    }
}
