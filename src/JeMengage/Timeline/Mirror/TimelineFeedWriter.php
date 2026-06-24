<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Mirror;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Writes the flat timeline_feed mirror table via DBAL.
 *
 * Uses INSERT ... ON DUPLICATE KEY UPDATE on the uuid unique key (the source item UUID, carried
 * by TimelineFeedDocument::$objectId): re-processing the same message is a functional no-op
 * (idempotent), and no ORM flush is involved (safe even when handled inline during an ORM flush,
 * as in the test sync transport).
 */
class TimelineFeedWriter
{
    public function __construct(
        private readonly Connection $connection,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function upsert(TimelineFeedDocument $document): void
    {
        if ($document->isRemoval()) {
            throw new \InvalidArgumentException('Cannot upsert a removal TimelineFeedDocument.');
        }

        $this->connection->executeStatement(
            'INSERT INTO timeline_feed (uuid, type, publication_date, event_date, audience, display, visibility, committee_uuid, agora_uuid, updated_at)
             VALUES (:uuid, :type, :publication_date, :event_date, :audience, :display, :visibility, :committee_uuid, :agora_uuid, :now)
             ON DUPLICATE KEY UPDATE type = VALUES(type), publication_date = VALUES(publication_date),
                event_date = VALUES(event_date), audience = VALUES(audience), display = VALUES(display),
                visibility = VALUES(visibility), committee_uuid = VALUES(committee_uuid), agora_uuid = VALUES(agora_uuid),
                updated_at = VALUES(updated_at)',
            [
                'uuid' => $document->objectId->toRfc4122(),
                'type' => $document->type,
                'publication_date' => $document->publicationDate,
                'event_date' => $document->eventDate,
                'audience' => $document->audience,
                'display' => $document->display,
                'visibility' => $document->visibility,
                'committee_uuid' => $document->committeeUuid,
                'agora_uuid' => $document->agoraUuid,
                'now' => new \DateTimeImmutable(),
            ],
            [
                'publication_date' => Types::DATETIME_IMMUTABLE,
                'event_date' => Types::DATETIME_IMMUTABLE,
                'audience' => Types::JSON,
                'display' => Types::JSON,
                'now' => Types::DATETIME_IMMUTABLE,
            ],
        );

        $this->logger->debug('TimelineFeed upsert', ['uuid' => $document->objectId->toRfc4122()]);
    }

    public function delete(Uuid $objectId): void
    {
        $this->connection->executeStatement(
            'DELETE FROM timeline_feed WHERE uuid = :uuid',
            ['uuid' => $objectId->toRfc4122()],
        );

        $this->logger->debug('TimelineFeed delete', ['uuid' => $objectId->toRfc4122()]);
    }

    /**
     * Counts the rows a sweep would delete for the given threshold — used to show the blast radius
     * before the destructive DELETE.
     */
    public function countStaleBefore(\DateTimeImmutable $threshold): int
    {
        return (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM timeline_feed WHERE updated_at < :threshold',
            ['threshold' => $threshold],
            ['threshold' => Types::DATETIME_IMMUTABLE],
        );
    }

    /**
     * Removes rows untouched since the given threshold — i.e. items a full reindex did not
     * re-upsert because they no longer exist. Live upserts during the rebuild keep their row
     * fresh (updated_at >= threshold), so they survive the sweep.
     */
    public function deleteStaleBefore(\DateTimeImmutable $threshold): int
    {
        $deleted = (int) $this->connection->executeStatement(
            'DELETE FROM timeline_feed WHERE updated_at < :threshold',
            ['threshold' => $threshold],
            ['threshold' => Types::DATETIME_IMMUTABLE],
        );

        $this->logger->info('TimelineFeed stale rows swept', ['threshold' => $threshold->format('c'), 'deleted_rows' => $deleted]);

        return $deleted;
    }
}
