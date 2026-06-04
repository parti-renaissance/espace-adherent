<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Mirror\Message;

use App\Messenger\Message\UuidDefaultAsyncMessage;
use Symfony\Component\Uid\Uuid;

/**
 * Asynchronously (re)index one timeline source entity into the timeline_feed mirror.
 *
 * Shared by the live indexing path (decorator) and the single-item reindex command — one
 * upsert path (DRY). The handler reloads the entity, so the committed state is always indexed.
 */
class UpsertTimelineFeedCommand extends UuidDefaultAsyncMessage
{
    public function __construct(
        public readonly string $entityClass,
        public readonly int|string $entityId,
    ) {
        parent::__construct(Uuid::v4());
    }
}
