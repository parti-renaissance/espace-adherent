<?php

namespace App\Telegram;

interface BotProviderInterface
{
    public function loadByIdentifier(string $identifier): ?BotInterface;
}
