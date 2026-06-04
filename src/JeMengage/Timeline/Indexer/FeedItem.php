<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Indexer;

/**
 * A single entry of the indexer get_items response (call-get-items.txt): the external_id is the only
 * field the read path consumes (it is the timeline_feed UUID used to hydrate the display). `kind` and
 * `hotScore` are carried for completeness/telemetry but are not used to filter or re-rank at read time
 * (the indexer is the ranking authority, order is preserved as returned).
 */
class FeedItem
{
    public function __construct(
        public readonly string $externalId,
        public readonly string $kind,
        public readonly float $hotScore,
    ) {
    }
}
