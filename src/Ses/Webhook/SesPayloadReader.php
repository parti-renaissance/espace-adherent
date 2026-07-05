<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

use Symfony\Component\Uid\Uuid;

class SesPayloadReader
{
    public const string TAG_CAMPAIGN_UUID = 'campaign_uuid';
    public const string TAG_ADHERENT_UUID = 'adherent_uuid';

    /**
     * @return array<string, mixed> the decoded SES event, or [] when the envelope carries no decodable event
     */
    public function decode(array $snsPayload): array
    {
        $message = $snsPayload['Message'] ?? null;
        if (!\is_string($message)) {
            return [];
        }

        $decoded = json_decode($message, true);

        return \is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $decodedEvent
     */
    public function readAttribution(array $decodedEvent): ?SesAttribution
    {
        $tags = $decodedEvent['mail']['tags'] ?? [];

        $campaignUuid = $this->readUuidTag($tags, self::TAG_CAMPAIGN_UUID);
        $adherentUuid = $this->readUuidTag($tags, self::TAG_ADHERENT_UUID);
        if (null === $campaignUuid || null === $adherentUuid) {
            return null;
        }

        return new SesAttribution($campaignUuid, $adherentUuid);
    }

    public function readUuidTag(mixed $tags, string $name): ?Uuid
    {
        $value = $this->readUuidStringTag($tags, $name);

        return null !== $value ? Uuid::fromString($value) : null;
    }

    public function readUuidStringTag(mixed $tags, string $name): ?string
    {
        $value = \is_array($tags) && \is_array($tags[$name] ?? null) ? ($tags[$name][0] ?? null) : null;

        return \is_string($value) && Uuid::isValid($value) ? $value : null;
    }

    /**
     * Bounds a free-form string to the target column length, or returns null for empty/non-string input.
     */
    public function clip(mixed $value, int $max): ?string
    {
        if (!\is_string($value) || '' === $value) {
            return null;
        }

        return mb_substr($value, 0, $max);
    }

    public function readTimestamp(mixed $rawTimestamp): ?\DateTimeImmutable
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
