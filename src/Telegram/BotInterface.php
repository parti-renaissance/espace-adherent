<?php

namespace App\Telegram;

interface BotInterface
{
    public function getIdentifier(): string;

    public function isEnabled(): bool;

    public function getApiToken(): string;

    public function getSecret(): string;

    public function getBlacklistedChatIds(): array;

    public function getWhitelistedChatIds(): array;
}
