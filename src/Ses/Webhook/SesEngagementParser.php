<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

use Symfony\Component\Uid\Uuid;

class SesEngagementParser
{
    public function parse(array $snsPayload): ?SesEngagementEvent
    {
        $event = $this->decodeSesEvent($snsPayload);
        if (null === $event) {
            return null;
        }

        $type = match ($event['eventType'] ?? null) {
            'Open' => SesEngagementType::OPEN,
            'Click' => SesEngagementType::CLICK,
            default => null,
        };
        if (null === $type) {
            return null;
        }

        $tags = $event['mail']['tags'] ?? [];
        $campaignUuid = $this->readUuidTag($tags, 'campaign_uuid');
        $adherentUuid = $this->readUuidTag($tags, 'adherent_uuid');
        if (null === $campaignUuid || null === $adherentUuid) {
            return null;
        }

        $section = SesEngagementType::OPEN === $type ? 'open' : 'click';
        $occurredAt = $this->readTimestamp($event[$section]['timestamp'] ?? null);
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

        return new SesEngagementEvent($type, $campaignUuid, $adherentUuid, $occurredAt, $url);
    }

    private function decodeSesEvent(array $snsPayload): ?array
    {
        $message = $snsPayload['Message'] ?? null;
        if (!\is_string($message)) {
            return null;
        }

        $decoded = json_decode($message, true);

        return \is_array($decoded) ? $decoded : null;
    }

    /**
     * @param array<string, mixed> $tags
     */
    private function readUuidTag(array $tags, string $name): ?Uuid
    {
        // SES exposes each tag value as a list (e.g. {"campaign_uuid": ["<uuid>"]}).
        $value = $tags[$name][0] ?? null;
        if (!\is_string($value) || !Uuid::isValid($value)) {
            return null;
        }

        return Uuid::fromString($value);
    }

    private function readTimestamp(mixed $rawTimestamp): ?\DateTimeImmutable
    {
        if (!\is_string($rawTimestamp) || '' === $rawTimestamp) {
            return null;
        }

        try {
            return new \DateTimeImmutable($rawTimestamp)->setTimezone(new \DateTimeZone('UTC'));
        } catch (\Exception) {
            return null;
        }
    }
}
