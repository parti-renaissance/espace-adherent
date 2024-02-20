<?php

namespace App\Telegram\Command;

use App\Telegram\Message;

class SendBotMessageCommand
{
    public function __construct(
        public readonly string $botIdentifier,
        public readonly Message $message
    ) {
    }
}
