<?php

namespace Tests\App\Telegram\Client;

use App\Telegram\Client\ClientInterface;

class Client implements ClientInterface
{
    public function sendMessage(string $chatId, string $text): void
    {
    }

    public function setWebhook(string $url): void
    {
    }

    public function deleteWebhook(): void
    {
    }
}
