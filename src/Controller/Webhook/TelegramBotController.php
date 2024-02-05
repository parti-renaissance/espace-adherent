<?php

namespace App\Controller\Webhook;

use App\Telegram\WebhookRequestHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(self::ROUTE_PATH, name: self::ROUTE_NAME, methods: ['POST'])]
class TelegramBotController
{
    public const ROUTE_NAME = 'webhook_telegram_bot';
    public const ROUTE_PATH = '/telegram';

    public function __construct(private readonly WebhookRequestHandler $requestHandler)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        return $this->requestHandler->handle($request);
    }
}
