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
    private const SECRET_TOKEN_HEADER = 'X-Telegram-Bot-Api-Secret-Token';

    public function __construct(
        private readonly BotProviderInterface $botProvider,
        private readonly UpdateHandler $updateHandler,
        private readonly Logger $logger
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        if (!$secret = $request->headers->get(self::SECRET_TOKEN_HEADER)) {
            throw new BadRequestHttpException('The request has no secret token header.');
        }

        if (!$bot = $this->botProvider->findOneEnabledBySecret($secret)) {
            throw new BadRequestHttpException('No bot found for given secret token.');
        }

        $this->logger->log($bot, 'Received webhook request.');

        if (!$content = $request->getContent()) {
            throw new BadRequestHttpException('No content in request');
        }

        try {
            $data = BotApi::jsonValidate($content, true);
        } catch (InvalidJsonException $e) {
            throw new BadRequestHttpException('The request content is not a valid JSON payload.');
        }

        try {
            $update = Update::fromResponse($data);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException('The request content is not a valid Telegram webhook.');
        }

        $this->logger->log($bot, 'Handling webhook request.');

        $this->updateHandler->handle($bot, $update);

        return new JsonResponse('OK');
    }
}
