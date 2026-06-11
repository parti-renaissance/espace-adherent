<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\Mirror;

use App\JeMengage\Timeline\Mirror\TimelineFeedDocument;
use App\JeMengage\Timeline\Mirror\TimelineFeedWriter;
use Doctrine\DBAL\Connection;
use Psr\Log\NullLogger;
use Symfony\Component\Uid\Uuid;
use Tests\App\AbstractKernelTestCase;

class TimelineFeedWriterTest extends AbstractKernelTestCase
{
    private ?Connection $connection = null;
    private ?TimelineFeedWriter $writer = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->manager->getConnection();
        $this->writer = new TimelineFeedWriter($this->connection, new NullLogger());

        // The shared test DB is pre-populated by fixtures (the live decorator mirrors timeline
        // entities synchronously during fixtures:load); isolate this writer test from those rows.
        $this->connection->executeStatement('DELETE FROM timeline_feed');
    }

    protected function tearDown(): void
    {
        $this->connection = null;
        $this->writer = null;

        parent::tearDown();
    }

    public function testUpsertIsIdempotent(): void
    {
        $uuid = Uuid::v4();
        $document = $this->document($uuid, ['objectID' => $uuid->toRfc4122(), 'title' => 'Hello']);

        $this->writer->upsert($document);
        $this->writer->upsert($document);

        $rows = $this->rows();
        self::assertCount(1, $rows);
        self::assertSame($uuid->toRfc4122(), $rows[0]['uuid']);
        self::assertSame('event', $rows[0]['type']);
        // MySQL JSON columns normalise (reorder) object keys, so compare order-insensitively.
        self::assertEquals(['objectID' => $uuid->toRfc4122(), 'title' => 'Hello'], json_decode((string) $rows[0]['display'], true));
    }

    public function testUpsertUpdatesSyncOwnedColumns(): void
    {
        $uuid = Uuid::v4();
        $this->writer->upsert($this->document($uuid, ['title' => 'Before']));
        $this->writer->upsert($this->document($uuid, ['title' => 'After'], 'news', ['include' => ['national' => true]]));

        $rows = $this->rows();
        self::assertCount(1, $rows);
        self::assertSame('news', $rows[0]['type']);
        self::assertEquals(['title' => 'After'], json_decode((string) $rows[0]['display'], true));
        self::assertEquals(['include' => ['national' => true]], json_decode((string) $rows[0]['audience'], true));
    }

    public function testUpsertDoesNotOverwriteOperatorOwnedAuthorImportance(): void
    {
        $uuid = Uuid::v4();
        $this->writer->upsert($this->document($uuid, ['title' => 'First']));

        // Operator tunes the signal directly in DB; a later sync upsert must preserve it.
        $this->connection->executeStatement(
            'UPDATE timeline_feed SET author_importance = 4 WHERE uuid = :uuid',
            ['uuid' => $uuid->toRfc4122()],
        );
        $this->writer->upsert($this->document($uuid, ['title' => 'Edited']));

        $rows = $this->rows();
        self::assertSame(4, (int) $rows[0]['author_importance']);
        self::assertEquals(['title' => 'Edited'], json_decode((string) $rows[0]['display'], true));
    }

    public function testNewRowGetsDefaultAuthorImportance(): void
    {
        $uuid = Uuid::v4();
        $this->writer->upsert($this->document($uuid, ['title' => 'Fresh']));

        self::assertSame(1, (int) $this->rows()[0]['author_importance']);
    }

    public function testUpsertRejectsRemovalDocument(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->writer->upsert(new TimelineFeedDocument(Uuid::v4(), null, null, null, null, null));
    }

    public function testDeleteRemovesRow(): void
    {
        $uuid = Uuid::v4();
        $this->writer->upsert($this->document($uuid, ['title' => 'Hello']));
        $this->writer->delete($uuid);

        self::assertCount(0, $this->rows());
    }

    public function testDeleteStaleBeforeSweepsUntouchedRows(): void
    {
        // Seed a row with an old updated_at, then run a "rebuild" that only re-upserts one of them.
        $this->connection->executeStatement(
            "INSERT INTO timeline_feed (uuid, type, publication_date, display, updated_at)
             VALUES ('stale', 'news', '2000-01-01 00:00:00', '{}', '2000-01-01 00:00:00')",
        );

        $threshold = new \DateTimeImmutable();
        $fresh = Uuid::v4();
        $this->writer->upsert($this->document($fresh, ['title' => 'Fresh']));

        $deleted = $this->writer->deleteStaleBefore($threshold);

        self::assertSame(1, $deleted);
        $rows = $this->rows();
        self::assertCount(1, $rows);
        self::assertSame($fresh->toRfc4122(), $rows[0]['uuid']);
    }

    private function document(Uuid $uuid, array $display, string $type = 'event', ?array $audience = null): TimelineFeedDocument
    {
        return new TimelineFeedDocument(
            $uuid,
            $type,
            new \DateTimeImmutable('2026-01-01 10:00:00'),
            null,
            $audience,
            $display,
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function rows(): array
    {
        return $this->connection->fetchAllAssociative('SELECT * FROM timeline_feed ORDER BY id');
    }
}
