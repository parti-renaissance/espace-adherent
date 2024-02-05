<?php

namespace App\Telegram\Webhook;

use App\Telegram\BotInterface;
use App\Telegram\Client\ClientFactoryInterface;
use App\Telegram\Client\ClientInterface;

class UrlHandler
{
    public function __construct(
        private readonly ClientFactoryInterface $clientFactory,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function setWebhook(BotInterface $bot): void
    {
        $webhookUrl = $this->urlGenerator->generateUrl($bot);

        $this
            ->createClient($bot)
            ->setWebhook($webhookUrl, $bot->getSecret())
        ;
    }

    public function removeWebhook(BotInterface $bot): void
    {
        $this
            ->createClient($bot)
            ->deleteWebhook()
        ;
    }

    private function createClient(BotInterface $bot): ClientInterface
    {
        return $this->clientFactory->createClient($bot);
    }
}
