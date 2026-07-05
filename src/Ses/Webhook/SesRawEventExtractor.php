<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

class SesRawEventExtractor
{
    public function __construct(private readonly SesPayloadReader $reader)
    {
    }

    public function extract(array $snsPayload): SesRawEventData
    {
        $snsMessageId = $this->reader->clip($snsPayload['MessageId'] ?? null, 255) ?? '';

        $event = $this->reader->decode($snsPayload);

        $kind = $event['eventType'] ?? $event['notificationType'] ?? null;
        $kind = \is_string($kind) ? $kind : null;

        $tags = $event['mail']['tags'] ?? [];

        return new SesRawEventData(
            $snsMessageId,
            $this->reader->clip($kind, 50),
            $this->reader->clip($event['mail']['messageId'] ?? null, 255),
            $this->reader->readUuidStringTag($tags, SesPayloadReader::TAG_CAMPAIGN_UUID),
            $this->reader->readUuidStringTag($tags, SesPayloadReader::TAG_ADHERENT_UUID),
            $this->reader->clip($this->extractRecipient($event, $kind), 255),
            $this->extractOccurredAt($event, $kind),
            $snsPayload,
        );
    }

    private function extractRecipient(array $event, ?string $kind): mixed
    {
        $specific = match ($kind) {
            'Bounce' => $event['bounce']['bouncedRecipients'][0]['emailAddress'] ?? null,
            'Complaint' => $event['complaint']['complainedRecipients'][0]['emailAddress'] ?? null,
            'DeliveryDelay' => $event['deliveryDelay']['delayedRecipients'][0]['emailAddress'] ?? null,
            default => null,
        };

        if (\is_string($specific) && '' !== $specific) {
            return $specific;
        }

        return $event['mail']['destination'][0] ?? null;
    }

    private function extractOccurredAt(array $event, ?string $kind): ?\DateTimeImmutable
    {
        $rawTimestamp = match ($kind) {
            'Open' => $event['open']['timestamp'] ?? null,
            'Click' => $event['click']['timestamp'] ?? null,
            'Delivery' => $event['delivery']['timestamp'] ?? null,
            'DeliveryDelay' => $event['deliveryDelay']['timestamp'] ?? null,
            'Bounce' => $event['bounce']['timestamp'] ?? null,
            'Complaint' => $event['complaint']['timestamp'] ?? null,
            'Send', 'Reject' => $event['mail']['timestamp'] ?? null,
            default => null,
        };

        return $this->reader->readTimestamp($rawTimestamp);
    }
}
