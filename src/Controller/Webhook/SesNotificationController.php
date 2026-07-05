<?php

declare(strict_types=1);

namespace App\Controller\Webhook;

use App\Ses\Webhook\Command\RecordSesRawEventCommand;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/ses/notification/{key}', name: 'app_ses_notification_webhook', methods: ['POST'])]
class SesNotificationController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly LoggerInterface $logger,
        private readonly string $sesWebhookKey,
        private readonly string $sesNotificationTopicArn,
    ) {
    }

    public function __invoke(string $key, Request $request): Response
    {
        if ('' === $this->sesWebhookKey || !hash_equals($this->sesWebhookKey, $key)) {
            $this->logger->error('[SES][Webhook] Rejected notification: invalid or unconfigured webhook key');

            return new Response('Forbidden', Response::HTTP_FORBIDDEN);
        }

        $payload = $request->toArray();

        if (($payload['TopicArn'] ?? null) !== $this->sesNotificationTopicArn) {
            $this->logger->error('[SES][Webhook] Rejected notification: unexpected TopicArn', [
                'topic_arn' => $payload['TopicArn'] ?? null,
            ]);

            return new Response('Forbidden', Response::HTTP_FORBIDDEN);
        }

        $type = $payload['Type'] ?? null;

        if ('SubscriptionConfirmation' === $type) {
            $this->logger->warning('[SES][Webhook] SNS SubscriptionConfirmation received — confirm it via infra', [
                'subscribe_url' => $payload['SubscribeURL'] ?? null,
            ]);

            return new Response('OK');
        }

        if ('Notification' === $type) {
            $this->bus->dispatch(new RecordSesRawEventCommand($payload, new \DateTimeImmutable()));

            return new Response('OK');
        }

        $this->logger->info('[SES][Webhook] Ignored SNS message of unhandled type', ['type' => $type]);

        return new Response('OK');
    }
}
