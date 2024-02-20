<?php

namespace Tests\App\Telegram\Client;

use App\Telegram\BotInterface;
use App\Telegram\Client\ClientFactoryInterface;
use App\Telegram\Client\ClientInterface;

class ClientFactory implements ClientFactoryInterface
{
    public function createClient(BotInterface $bot): ClientInterface
    {
        return new Client();
    }
}
