<?php

namespace App\Telegram\Client;

interface ClientInterface
{
    public function sendMessage(string $chatId, string $text, array $entities = []): void;

    public function setWebhook(string $url, string $secret): void;

    public function deleteWebhook(): void;
}
