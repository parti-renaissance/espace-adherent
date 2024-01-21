<?php

namespace Tests\App\Telegram\Client;

use App\Entity\Chatbot\Chatbot;
use App\Telegram\Client\ClientFactoryInterface;
use App\Telegram\Client\ClientInterface;

class ClientFactory implements ClientFactoryInterface
{
    public function createClient(Chatbot $chatbot): ClientInterface
    {
        return new Client();
    }
}
