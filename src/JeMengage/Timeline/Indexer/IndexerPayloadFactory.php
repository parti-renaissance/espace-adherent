<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Indexer;

use App\Entity\Timeline\TimelineFeed;
use Psr\Log\LoggerInterface;

/**
 * Pure projection of a timeline_feed row to an indexer ItemPayload.
 *
 * The canonical audience is sent as-is: the indexer is the matching authority and validates only
 * author_importance (call-indexer.txt), so it accepts the full targeting (every dimension, every
 * zone type) and ignores what it cannot match yet. Sending everything is forward-compatible — no
 * backfill is needed when the indexer gains a dimension. author_importance is clamped to the
 * indexer's [1,5] range (the single validation, otherwise a 422 retry loop) and read fresh from the
 * row (operator-owned). Returns null only when the type is not pushable.
 */
class IndexerPayloadFactory
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function create(TimelineFeed $row): ?ItemPayload
    {
        $externalId = $row->getUuid()->toRfc4122();

        $kind = IndexerKind::fromInternalType($row->type);
        if (null === $kind) {
            $this->logger->debug('Timeline indexer: not pushable, skipped.', ['external_id' => $externalId, 'type' => $row->type]);

            return null;
        }

        return new ItemPayload(
            $externalId,
            $kind->value,
            $row->publicationDate,
            $row->eventDate,
            max(1, min(5, $row->authorImportance)),
            $row->audience,
        );
    }
}
