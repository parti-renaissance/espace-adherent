<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

use Symfony\Component\Uid\Uuid;

class SesDeliveryDelayEvent implements AttributableSesEvent
{
    public function __construct(
        public readonly Uuid $campaignUuid,
        public readonly Uuid $adherentUuid,
        public readonly \DateTimeImmutable $delayedAt,
        public readonly ?string $delayType,
    ) {
    }
}
