<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

class SesEngagementParser implements AttributableSesEventParser
{
    public function __construct(private readonly SesPayloadReader $reader)
    {
    }

    public function parse(array $snsPayload): ?SesEngagementEvent
    {
        $event = $this->reader->decode($snsPayload);

        $type = match ($event['eventType'] ?? null) {
            'Open' => SesEngagementType::OPEN,
            'Click' => SesEngagementType::CLICK,
            default => null,
        };
        if (null === $type) {
            return null;
        }

        $attribution = $this->reader->readAttribution($event);
        if (null === $attribution) {
            return null;
        }

        $section = SesEngagementType::OPEN === $type ? 'open' : 'click';
        $occurredAt = $this->reader->readTimestamp($event[$section]['timestamp'] ?? null);
        if (null === $occurredAt) {
            return null;
        }

        $url = null;
        if (SesEngagementType::CLICK === $type) {
            $url = $event['click']['link'] ?? null;
            if (!\is_string($url) || '' === $url) {
                // A click without a usable link cannot be recorded as a click hit.
                return null;
            }
        }

        return new SesEngagementEvent($type, $attribution->campaignUuid, $attribution->adherentUuid, $occurredAt, $url);
    }
}
