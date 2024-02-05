<?php

namespace App\Telegram\EventListener;

use App\Telegram\Command\SendBotMessageCommand;
use App\Telegram\Event\BotMessageEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsEventListener]
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
