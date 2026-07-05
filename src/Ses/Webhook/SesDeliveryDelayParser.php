<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

class SesDeliveryDelayParser implements AttributableSesEventParser
{
    public function __construct(private readonly SesPayloadReader $reader)
    {
    }

    public function parse(array $snsPayload): ?SesDeliveryDelayEvent
    {
        $event = $this->reader->decode($snsPayload);

        if ('DeliveryDelay' !== ($event['eventType'] ?? null)) {
            return null;
        }

        $attribution = $this->reader->readAttribution($event);
        if (null === $attribution) {
            return null;
        }

        $delayedAt = $this->reader->readTimestamp($event['deliveryDelay']['timestamp'] ?? null);
        if (null === $delayedAt) {
            return null;
        }

        return new SesDeliveryDelayEvent(
            $attribution->campaignUuid,
            $attribution->adherentUuid,
            $delayedAt,
            $this->reader->clip($event['deliveryDelay']['delayType'] ?? null, 255),
        );
    }
}
