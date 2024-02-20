<?php

namespace Tests\App\Telegram\Client;

use App\Telegram\Client\ClientInterface;

class Client implements ClientInterface
{
    public function sendMessage(string $chatId, string $text, array $entities = []): void
    {
    }

    public function setWebhook(string $url, string $secret): void
    {
    }

    public function deleteWebhook(): void
    {
    }
}
