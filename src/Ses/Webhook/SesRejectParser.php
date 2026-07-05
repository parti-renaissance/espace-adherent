<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

class SesRejectParser implements AttributableSesEventParser
{
    public function __construct(private readonly SesPayloadReader $reader)
    {
    }

    public function parse(array $snsPayload): ?SesRejectEvent
    {
        $event = $this->reader->decode($snsPayload);

        if ('Reject' !== ($event['eventType'] ?? null)) {
            return null;
        }

        $attribution = $this->reader->readAttribution($event);
        if (null === $attribution) {
            return null;
        }

        $rejectedAt = $this->reader->readTimestamp($event['mail']['timestamp'] ?? null);
        if (null === $rejectedAt) {
            return null;
        }

        $reason = $this->reader->clip($event['reject']['reason'] ?? null, 255);

        return new SesRejectEvent($attribution->campaignUuid, $attribution->adherentUuid, $rejectedAt, $reason);
    }
}
