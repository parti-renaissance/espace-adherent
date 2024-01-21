<?php

namespace App\Telegram\Update;

use App\Telegram\BotInterface;
use TelegramBot\Api\Types\Update;

interface UpdateHandlerInterface
{
    public function supports(BotInterface $bot, Update $update): bool;

    public function handle(BotInterface $bot, Update $update): void;
}
