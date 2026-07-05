<?php

declare(strict_types=1);

namespace App\Ses\Webhook\Command;

use App\Ses\Webhook\SesWebhookMessageInterface;

class RecordSesRawEventCommand implements SesWebhookMessageInterface
{
    public function __construct(
        public readonly array $payload,
        public readonly \DateTimeImmutable $receivedAt,
    ) {
    }
}
