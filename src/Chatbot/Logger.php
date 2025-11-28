<?php

declare(strict_types=1);

namespace App\Chatbot;

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

    public function log(string $message, array $context = []): void
    {
        if (!$this->logger) {
            return;
        }

        $this->logger->info('[Chatbot] '.$message, $context);
    }

    public function logThread(Thread $thread, string $message, array $context = []): void
    {
        $this->log(
            \sprintf(
                '[Thread:%s] %s',
                $thread->getUuid()->toString(),
                $message
            ),
            $context
        );
    }
}
