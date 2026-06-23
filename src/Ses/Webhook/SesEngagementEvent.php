<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

use Symfony\Component\Uid\Uuid;

class SesEngagementEvent
{
    public function __construct(
        public readonly SesEngagementType $type,
        public readonly Uuid $campaignUuid,
        public readonly Uuid $adherentUuid,
        public readonly \DateTimeImmutable $occurredAt,
        public readonly ?string $url = null,
    ) {
    }
}
