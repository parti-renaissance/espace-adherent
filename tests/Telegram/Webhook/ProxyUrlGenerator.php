<?php

namespace Tests\App\Telegram\Webhook;

use App\Controller\Webhook\TelegramBotController;
use App\Telegram\BotInterface;
use App\Telegram\Webhook\UrlGeneratorInterface;

class ProxyUrlGenerator implements UrlGeneratorInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ?string $webhookProxyHost = null
    ) {
    }

    public function generateUrl(BotInterface $bot): string
    {
        if (!empty($this->webhookProxyHost)) {
            return sprintf(
                '%s%s',
                $this->webhookProxyHost,
                TelegramBotController::ROUTE_PATH
            );
        }

        return $this->urlGenerator->generateUrl($bot);
    }
}
