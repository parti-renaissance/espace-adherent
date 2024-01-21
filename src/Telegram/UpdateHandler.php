<?php

namespace App\Telegram;

use App\Telegram\Update\UpdateHandlerInterface;
use TelegramBot\Api\Types\Update;

class UpdateHandler
{
    /**
     * @param UpdateHandlerInterface[]|iterable $updateHandlers
     */
    public function __construct(private readonly iterable $updateHandlers = [])
    {
    }

    public function handle(BotInterface $bot, Update $update): void
    {
        if (!$bot->isEnabled()) {
            return;
        }

        foreach ($this->updateHandlers as $updateHandler) {
            if ($updateHandler->supports($bot, $update)) {
                $updateHandler->handle($bot, $update);

                return;
            }
        }
    }
}
