<?php

declare(strict_types=1);

namespace App\Chatbot\Handler;

use App\Chatbot\Command\SendTelegramMessageCommand;
use App\Chatbot\Telegram\MessageHandler;
use App\Repository\Chatbot\MessageRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendTelegramMessageCommandHandler
{
    public function __construct(
        private readonly MessageRepository $messageRepository,
        private readonly MessageHandler $messageHandler,
    ) {
    }

    public function __invoke(SendTelegramMessageCommand $command): void
    {
        $message = $this->messageRepository->findOneByUuid($command->getUuid()->toString());

        if (!$message || $message->isUserMessage()) {
            return;
        }

        $this->messageHandler->sendMessage($message);
    }
}
