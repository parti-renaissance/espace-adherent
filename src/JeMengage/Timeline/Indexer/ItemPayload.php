<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Indexer;

/**
 * Immutable projection of a timeline_feed row to the external indexer item contract
 * (call-indexer.txt). Built by IndexerPayloadFactory and serialized as-is for the POST body.
 *
 * v1 does not track attendance/engagement, so they are emitted as their documented defaults
 * (null / 0-0). Datetimes are always sent as UTC with a trailing "Z".
 */
class ItemPayload implements \JsonSerializable
{
    public function __construct(
        public readonly string $externalId,
        public readonly string $kind,
        public readonly \DateTimeImmutable $publicationDate,
        public readonly ?\DateTimeImmutable $eventDate,
        public readonly int $authorImportance,
        public readonly ?array $audience,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'external_id' => $this->externalId,
            'kind' => $this->kind,
            'publication_date' => $this->toUtc($this->publicationDate),
            'event_date' => $this->eventDate ? $this->toUtc($this->eventDate) : null,
            'author_importance' => $this->authorImportance,
            'attendance' => null,
            'engagement' => ['likes' => 0, 'comments' => 0],
            'audience' => $this->audience,
        ];
    }

    private function toUtc(\DateTimeImmutable $date): string
    {
        return $date->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');
    }
}
