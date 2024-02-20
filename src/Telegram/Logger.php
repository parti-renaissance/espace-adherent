<?php

namespace App\Telegram;

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

    public function log(BotInterface $bot, string $message): void
    {
        $this->logger->info(
            sprintf(
                '[TelegramBot:%s] %s',
                $bot->getIdentifier(),
                $message
            )
        );
    }
}
