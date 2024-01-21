<?php

namespace App\Telegram\Listener;

use App\Telegram\Command\SendBotMessageCommand;
use App\Telegram\Event\BotMessageEvent;
use Symfony\Component\Messenger\MessageBusInterface;

class BotMessageListener
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public function __invoke(BotMessageEvent $event)
    {
        $bot = $event->bot;

        if (!$bot->isEnabled()) {
            return;
        }

        $this->bus->dispatch(new SendBotMessageCommand($bot->getIdentifier(), $event->message));
    }
}
