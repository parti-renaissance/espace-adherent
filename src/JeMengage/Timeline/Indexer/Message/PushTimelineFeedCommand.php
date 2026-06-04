<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Indexer\Message;

use App\Messenger\Message\UuidDefaultAsyncMessage;

/**
 * Asynchronously push one timeline_feed row to the external indexer.
 *
 * The inherited UUID is the row UUID (the indexer external_id) — built as
 * `new PushTimelineFeedCommand($row->getUuid())`. The handler re-reads the row at handle time, so
 * the latest committed state (including operator-owned signals) is what gets pushed.
 */
class PushTimelineFeedCommand extends UuidDefaultAsyncMessage
{
}
