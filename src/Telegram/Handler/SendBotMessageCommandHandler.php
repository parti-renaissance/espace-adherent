<?php

namespace App\Telegram\Handler;

use App\Telegram\BotProviderInterface;
use App\Telegram\Client\ClientFactoryInterface;
use App\Telegram\Command\SendBotMessageCommand;
use App\Telegram\Logger;

class SendBotMessageCommandHandler
{
    public function __construct(
        private readonly BotProviderInterface $botProvider,
        private readonly ClientFactoryInterface $clientFactory,
        private readonly Logger $logger
    ) {
    }

    public function __invoke(SendBotMessageCommand $command): void
    {
        $bot = $this->botProvider->loadByIdentifier($command->botIdentifier);

        if (!$bot || !$bot->isEnabled()) {
            return;
        }

        $message = $command->message;

        $this->logger->log($bot, sprintf('Sending message to chatId: "%s".', $message->chatId));

        $this
            ->clientFactory
            ->createClient($bot)
            ->sendMessage(
                $message->chatId,
                $message->text,
                $message->entities
            )
        ;
    }
}
