<?php

declare(strict_types=1);

namespace App\Sentry\Webhook\Command;

use App\Messenger\Message\AsynchronousMessageInterface;
use App\Messenger\Message\LockableMessageInterface;
use App\Sentry\Webhook\SentryEvent;

class SentryWebhookCommand implements AsynchronousMessageInterface, LockableMessageInterface
{
    public function __construct(public readonly SentryEvent $event)
    {
    }

    public function getLockKey(): string
    {
        return 'sentry_webhook_'.$this->event->issueId;
    }

    public function getLockTtl(): int
    {
        return 60;
    }

    public function isLockBlocking(): bool
    {
        return false;
    }
}
