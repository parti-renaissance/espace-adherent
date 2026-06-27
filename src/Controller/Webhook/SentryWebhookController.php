<?php

declare(strict_types=1);

namespace App\Controller\Webhook;

use App\Sentry\Webhook\Command\SentryWebhookCommand;
use App\Sentry\Webhook\SentryEventFactory;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/sentry/{key}', name: 'app_sentry_webhook', methods: ['POST'])]
class SentryWebhookController extends AbstractController
{
    public function __construct(private readonly string $sentryWebhookSecret)
    {
    }

    public function __invoke(string $key, Request $request, MessageBusInterface $bus, LoggerInterface $logger, SentryEventFactory $factory): Response
    {
        if (!hash_equals($this->sentryWebhookSecret, $key)) {
            return new Response('', Response::HTTP_UNAUTHORIZED);
        }

        $payload = json_decode($request->getContent(), true);

        if (!\is_array($payload) || !isset($payload['data']['event'])) {
            $logger->warning('[Sentry webhook] Malformed payload, ignored.');

            return new Response('OK');
        }

        $bus->dispatch(new SentryWebhookCommand($factory->fromPayload($payload)));

        return new Response('OK');
    }
}
