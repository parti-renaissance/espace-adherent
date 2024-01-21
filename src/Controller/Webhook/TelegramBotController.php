<?php

namespace App\Controller\Webhook;

use App\Entity\TelegramBot;
use App\Telegram\WebhookRequestHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/telegram/{secret}', name: self::ROUTE_NAME, methods: ['POST'])]
#[Entity('telegramBot', expr: 'repository.findOneEnabledBySecret(secret)')]
class TelegramBotController
{
    public const ROUTE_NAME = 'webhook_telegram_bot';

    public function __construct(private readonly WebhookRequestHandler $requestHandler)
    {
    }

    public function __invoke(TelegramBot $telegramBot, Request $request): JsonResponse
    {
        return $this->requestHandler->handle($telegramBot, $request);
    }
}
