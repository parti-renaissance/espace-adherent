<?php

namespace App\Telegram\Update;

use App\Telegram\BotInterface;
use App\Telegram\Event\UserMessageEvent;
use App\Telegram\Message;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use TelegramBot\Api\Types\Message as TelegramMessage;
use TelegramBot\Api\Types\Update;

class TextMessageHandler extends AbstractMessageHandler
{
    public function __construct(private readonly EventDispatcherInterface $dispatcher)
    {
    }

    public function handle(BotInterface $bot, Update $update): void
    {
        $message = $update->getMessage();

        $this->dispatcher->dispatch(
            new UserMessageEvent(
                $bot,
                new Message(
                    $message->getChat()->getId(),
                    $message->getText(),
                    $message->getEntities() ?? [],
                    new \DateTimeImmutable('@'.$message->getDate())
                )
            )
        );
    }

    protected function isHandledMessage(TelegramMessage $message): bool
    {
        return null !== $message->getText();
    }
}
