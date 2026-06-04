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
    private const CHUNK_SIZE = 500;

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
            'INSERT INTO timeline_feed (uuid, type, publication_date, event_date, audience, display, updated_at)
             VALUES (:uuid, :type, :publication_date, :event_date, :audience, :display, :now)
             ON DUPLICATE KEY UPDATE type = VALUES(type), publication_date = VALUES(publication_date),
                event_date = VALUES(event_date), audience = VALUES(audience), display = VALUES(display),
                updated_at = VALUES(updated_at)',
            [
                'uuid' => $document->objectId->toRfc4122(),
                'type' => $document->type,
                'publication_date' => $document->publicationDate,
                'event_date' => $document->eventDate,
                'audience' => $document->audience,
                'display' => $document->display,
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

    /**
     * @param TimelineFeedDocument[] $documents
     */
    public function bulkUpsert(array $documents): int
    {
        foreach (array_chunk($documents, self::CHUNK_SIZE) as $chunk) {
            $this->upsertChunk($chunk);
        }

        return \count($documents);
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

    /**
     * @param TimelineFeedDocument[] $documents non-empty, already bounded to CHUNK_SIZE
     */
    private function upsertChunk(array $documents): void
    {
        $now = new \DateTimeImmutable();
        $rows = [];
        $params = [];
        $types = [];

        foreach (array_values($documents) as $i => $document) {
            if ($document->isRemoval()) {
                throw new \InvalidArgumentException('Cannot upsert a removal TimelineFeedDocument.');
            }

            $rows[] = \sprintf('(:u%1$d, :t%1$d, :p%1$d, :e%1$d, :a%1$d, :d%1$d, :n%1$d)', $i);
            $params["u$i"] = $document->objectId->toRfc4122();
            $params["t$i"] = $document->type;
            $params["p$i"] = $document->publicationDate;
            $params["e$i"] = $document->eventDate;
            $params["a$i"] = $document->audience;
            $params["d$i"] = $document->display;
            $params["n$i"] = $now;
            $types["p$i"] = Types::DATETIME_IMMUTABLE;
            $types["e$i"] = Types::DATETIME_IMMUTABLE;
            $types["a$i"] = Types::JSON;
            $types["d$i"] = Types::JSON;
            $types["n$i"] = Types::DATETIME_IMMUTABLE;
        }

        $this->connection->executeStatement(
            'INSERT INTO timeline_feed (uuid, type, publication_date, event_date, audience, display, updated_at) VALUES '
            .implode(', ', $rows)
            .' ON DUPLICATE KEY UPDATE type = VALUES(type), publication_date = VALUES(publication_date),
               event_date = VALUES(event_date), audience = VALUES(audience), display = VALUES(display),
               updated_at = VALUES(updated_at)',
            $params,
            $types,
        );
    }
}
