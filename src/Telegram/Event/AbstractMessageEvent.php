<?php

namespace App\Telegram\Event;

use App\Telegram\BotInterface;
use App\Telegram\Message;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractMessageEvent extends Event
{
    final public function __construct(
        public readonly BotInterface $bot,
        public readonly Message $message
    ) {
    }
}
