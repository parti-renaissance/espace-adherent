<?php

namespace App\Telegram\Webhook;

use App\Telegram\BotInterface;

interface UrlGeneratorInterface
{
    public function generateUrl(BotInterface $bot): string;
}
