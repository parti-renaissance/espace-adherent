<?php

namespace App\Telegram;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\InvalidJsonException;
use TelegramBot\Api\Types\Update;

class WebhookRequestHandler
{
    public function __construct(
        private readonly UpdateHandler $updateHandler,
        private readonly Logger $logger
    ) {
    }

    public function handle(BotInterface $bot, Request $request): JsonResponse
    {
        $this->logger->log($bot, 'Received webhook request.');

        if (!$content = $request->getContent()) {
            throw new BadRequestHttpException('No content in request');
        }

        try {
            $data = BotApi::jsonValidate($content, true);

            $update = Update::fromResponse($data);
        } catch (InvalidArgumentException|InvalidJsonException $e) {
            throw new BadRequestHttpException('The request content is not a valid Telegram webhook.');
        }

        $this->logger->log($bot, 'Handling webhook request.');

        $this->updateHandler->handle($bot, $update);

        return new JsonResponse('OK');
    }
}
