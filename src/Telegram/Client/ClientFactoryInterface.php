<?php

namespace App\Telegram\Client;

use App\Telegram\BotInterface;

interface ClientFactoryInterface
{
    public function createClient(BotInterface $bot): ClientInterface;
}
