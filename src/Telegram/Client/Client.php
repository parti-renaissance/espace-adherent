<?php

namespace App\Telegram\Client;

use TelegramBot\Api\BotApi;

class Client implements ClientInterface
{
    public function __construct(private readonly BotApi $botApi)
    {
    }

    public function sendMessage(string $chatId, string $text, array $entities = []): void
    {
        $this->botApi->sendMessage($chatId, $text);
    }

    public function setWebhook(string $url, string $secret): void
    {
        $this->botApi->setWebhook($url, null, null, 40, null, false, $secret);
    }

    public function deleteWebhook(): void
    {
        $this->botApi->deleteWebhook();
    }
}
