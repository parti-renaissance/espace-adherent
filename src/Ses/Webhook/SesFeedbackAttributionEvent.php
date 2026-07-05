<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

use Symfony\Component\Uid\Uuid;

class SesFeedbackAttributionEvent implements AttributableSesEvent
{
    public function __construct(
        public readonly SesFeedbackType $type,
        public readonly Uuid $campaignUuid,
        public readonly Uuid $adherentUuid,
        public readonly \DateTimeImmutable $occurredAt,
        public readonly ?string $bounceSubType,
    ) {
    }
}
