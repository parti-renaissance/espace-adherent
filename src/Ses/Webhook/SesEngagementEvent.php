<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

use Symfony\Component\Uid\Uuid;

class SesEngagementEvent implements AttributableSesEvent
{
    public function __construct(
        public readonly SesEngagementType $type,
        public readonly Uuid $campaignUuid,
        public readonly Uuid $adherentUuid,
        public readonly \DateTimeImmutable $occurredAt,
        public readonly ?string $url = null,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null,
    ) {
    }
}
