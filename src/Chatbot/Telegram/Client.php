<?php

declare(strict_types=1);

namespace App\Chatbot\Telegram;

use TelegramBot\Api\BotApi;

class Client
{
    private const MAX_CONNECTIONS = 40;
    private const ALLOWED_UPDATE_TYPES = [
        'message',
    ];

    public function setWebhook(string $botApiToken, string $url): void
    {
        $this
            ->createClient($botApiToken)
            ->setWebhook($url, null, null, self::MAX_CONNECTIONS, self::ALLOWED_UPDATE_TYPES)
        ;
    }

    public function deleteWebhook(string $botApiToken): void
    {
        $this
            ->createClient($botApiToken)
            ->deleteWebhook(true)
        ;
    }

    public function sendMessage(string $botApiToken, string $chatId, string $messageText): void
    {
        $this->createClient($botApiToken)->sendMessage($chatId, $messageText);
    }

    private function createClient(string $botApiToken): BotApi
    {
        return new BotApi($botApiToken);
    }
}
