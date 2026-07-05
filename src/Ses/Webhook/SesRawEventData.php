<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

class SesRawEventData
{
    public function __construct(
        public readonly string $snsMessageId,
        public readonly ?string $eventType,
        public readonly ?string $sesMessageId,
        public readonly ?string $campaignUuid,
        public readonly ?string $adherentUuid,
        public readonly ?string $recipient,
        public readonly ?\DateTimeImmutable $occurredAt,
        public readonly array $payload,
    ) {
    }
}
