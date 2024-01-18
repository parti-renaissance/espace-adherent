<?php

namespace App\Chatbot\Handler;

use App\Chatbot\Command\SendTelegramMessageCommand;
use App\Chatbot\Telegram\MessageHandler;
use App\Repository\Chatbot\MessageRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SendTelegramMessageCommandHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly MessageRepository $messageRepository,
        private readonly MessageHandler $messageHandler
    ) {
    }

    public function __invoke(SendTelegramMessageCommand $command): void
    {
        $message = $this->messageRepository->findOneByUuid($command->getUuid()->toString());

        if (!$message || $message->isUserMessage()) {
            return;
        }

        $thread = $message->thread;

        if (
            !$thread->telegramChatId
            || !$thread->chatbot->enabled
            || !$thread->chatbot->telegramBotApiToken
        ) {
            return;
        }

        $this->messageHandler->sendMessage($message);
    }
}
