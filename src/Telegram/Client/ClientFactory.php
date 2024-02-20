<?php

namespace App\Telegram\Client;

use App\Telegram\BotInterface;
use TelegramBot\Api\BotApi;

class ClientFactory implements ClientFactoryInterface
{
    public function createClient(BotInterface $bot): ClientInterface
    {
        return new Client(new BotApi($bot->getApiToken()));
    }
}
