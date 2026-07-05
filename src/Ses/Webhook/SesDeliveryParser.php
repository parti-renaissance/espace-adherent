<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

class SesDeliveryParser implements AttributableSesEventParser
{
    public function __construct(private readonly SesPayloadReader $reader)
    {
    }

    public function parse(array $snsPayload): ?SesDeliveryEvent
    {
        $event = $this->reader->decode($snsPayload);

        if ('Delivery' !== ($event['eventType'] ?? null)) {
            return null;
        }

        $attribution = $this->reader->readAttribution($event);
        if (null === $attribution) {
            return null;
        }

        $deliveredAt = $this->reader->readTimestamp($event['delivery']['timestamp'] ?? null);
        if (null === $deliveredAt) {
            return null;
        }

        return new SesDeliveryEvent($attribution->campaignUuid, $attribution->adherentUuid, $deliveredAt);
    }
}
