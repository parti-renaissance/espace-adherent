<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

use App\Ses\Webhook\Command\ProcessSesNotificationCommand;
use App\Ses\Webhook\Command\RecordSesEngagementCommand;
use Symfony\Component\Messenger\MessageBusInterface;

class SesNotificationRouter
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public function dispatch(array $payload): void
    {
        if ($this->isEngagement($payload)) {
            $this->bus->dispatch(new RecordSesEngagementCommand($payload));

            return;
        }

        $this->bus->dispatch(new ProcessSesNotificationCommand($payload));
    }

    private function isEngagement(array $payload): bool
    {
        $message = $payload['Message'] ?? null;
        if (!\is_string($message)) {
            return false;
        }

        $decoded = json_decode($message, true);
        $eventType = \is_array($decoded) ? ($decoded['eventType'] ?? null) : null;

        return \in_array($eventType, ['Open', 'Click'], true);
    }
}
