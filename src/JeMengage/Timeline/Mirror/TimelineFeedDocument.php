<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Mirror;

use Symfony\Component\Uid\Uuid;

/**
 * Resolved canonical mirror model for a timeline source entity.
 *
 * `$objectId` is the source item UUID (the Algolia objectID, used as the indexer external_id).
 * `$display` is the normalizer record projected on the app contract; `$audience` is the derived
 * {include, exclude} targeting model (null = no constraint). `$visibility`/`$committeeUuid`/`$agoraUuid` are exposure
 * facts projected from the display (like `$eventDate`), promoted to mirror columns for the indexed
 * public read. A removal (entity no longer indexable) is signalled by a null `$display`, in which
 * case the mirror row should be deleted.
 */
class TimelineFeedDocument
{
    public function __construct(
        public readonly Uuid $objectId,
        public readonly ?string $type,
        public readonly ?\DateTimeImmutable $publicationDate,
        public readonly ?\DateTimeImmutable $eventDate,
        public readonly ?array $audience,
        public readonly ?array $display,
        public readonly ?string $visibility = null,
        public readonly ?string $committeeUuid = null,
        public readonly ?string $agoraUuid = null,
    ) {
    }

    public function isRemoval(): bool
    {
        return null === $this->display;
    }
}
