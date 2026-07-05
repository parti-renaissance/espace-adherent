<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

class SesFeedbackAttributionParser implements AttributableSesEventParser
{
    public function __construct(private readonly SesPayloadReader $reader)
    {
    }

    public function parse(array $snsPayload): ?SesFeedbackAttributionEvent
    {
        $event = $this->reader->decode($snsPayload);

        $type = match ($event['eventType'] ?? null) {
            'Bounce' => 'Permanent' === ($event['bounce']['bounceType'] ?? null) ? SesFeedbackType::HARD_BOUNCE : null,
            'Complaint' => SesFeedbackType::COMPLAINT,
            default => null,
        };
        if (null === $type) {
            return null;
        }

        $attribution = $this->reader->readAttribution($event);
        if (null === $attribution) {
            return null;
        }

        $occurredAt = $this->reader->readTimestamp(
            SesFeedbackType::HARD_BOUNCE === $type
                ? ($event['bounce']['timestamp'] ?? null)
                : ($event['complaint']['timestamp'] ?? null)
        );
        if (null === $occurredAt) {
            return null;
        }

        $bounceSubType = SesFeedbackType::HARD_BOUNCE === $type
            ? $this->reader->clip($event['bounce']['bounceSubType'] ?? null, 255)
            : null;

        return new SesFeedbackAttributionEvent($type, $attribution->campaignUuid, $attribution->adherentUuid, $occurredAt, $bounceSubType);
    }
}
