<?php

namespace App\Telegram\Webhook;

use App\Controller\Webhook\TelegramBotController;
use App\Telegram\BotInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface as SymfonyUrlGenerator;

class UrlGenerator implements UrlGeneratorInterface
{
    public function __construct(private readonly SymfonyUrlGenerator $urlGenerator)
    {
    }

    public function generateUrl(BotInterface $bot): string
    {
        return $this->urlGenerator->generate(
            TelegramBotController::ROUTE_NAME,
            [],
            SymfonyUrlGenerator::ABSOLUTE_URL
        );
    }
}
