<?php

declare(strict_types=1);

namespace App\Sentry\Webhook\Handler;

use App\Sentry\Webhook\Command\SentryWebhookCommand;
use App\Sentry\Webhook\Notifier\SentryChatMessageFactory;
use App\Sentry\Webhook\Routing\SentryEventRouter;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Notifier\ChatterInterface;

#[AsMessageHandler]
class SentryWebhookCommandHandler
{
    public function __construct(
        private readonly SentryEventRouter $router,
        private readonly SentryChatMessageFactory $messageFactory,
        private readonly ChatterInterface $chatter,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(SentryWebhookCommand $command): void
    {
        $event = $command->event;
        $decision = $this->router->route($event);

        if (null === $decision) {
            $this->logger->warning('[Sentry webhook] Unroutable event dropped.', [
                'project' => $event->projectId,
                'platform' => $event->platform,
                'environment' => $event->environment,
                'issue_id' => $event->issueId,
            ]);

            return;
        }

        foreach ($this->messageFactory->create($event, $decision) as $message) {
            try {
                $this->chatter->send($message);
            } catch (\Throwable $e) {
                $this->logger->error('[Sentry webhook] Destination failed.', [
                    'transport' => $message->getTransport(),
                    'issue_id' => $event->issueId,
                    'exception' => $e,
                ]);
            }
        }
    }
}
