<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\Mirror\Command;

use App\Entity\Jecoute\News;
use Doctrine\DBAL\Connection;
use Tests\App\AbstractCommandTestCase;

class TimelineFeedReindexCommandTest extends AbstractCommandTestCase
{
    private ?Connection $connection = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->manager->getConnection();
    }

    protected function tearDown(): void
    {
        $this->connection = null;

        parent::tearDown();
    }

    public function testReindexUpsertsItemsAndSweepsStaleRows(): void
    {
        // A ghost row for an object that no longer exists: the rebuild never re-upserts it, so the
        // timestamp sweep must remove it.
        $this->connection->executeStatement(
            "INSERT INTO timeline_feed (uuid, type, publication_date, display, updated_at)
             VALUES ('stale-object', 'news', '2000-01-01 00:00:00', '{}', '2000-01-01 00:00:00')",
        );

        $this->runCommand('app:timeline:reindex')->assertCommandIsSuccessful();

        self::assertGreaterThan(0, (int) $this->connection->fetchOne('SELECT COUNT(*) FROM timeline_feed'), 'At least one timeline item should be indexed.');
        self::assertFalse($this->rowExists('stale-object'), 'The stale row must be swept.');
    }

    public function testReindexRespectsIsIndexable(): void
    {
        $newsList = $this->getRepository(News::class)->findBy([], null, 2);
        self::assertCount(2, $newsList, 'Two News fixtures are required.');
        [$indexable, $nonIndexable] = $newsList;

        $indexable->setPublished(true);
        $indexable->setTitle('Reindexable News');
        $nonIndexable->setPublished(false);
        $this->manager->flush();

        // Capture the UUIDs before the command clears the entity manager.
        $indexableUuid = $indexable->getUuid()->toRfc4122();
        $nonIndexableUuid = $nonIndexable->getUuid()->toRfc4122();

        $this->runCommand('app:timeline:reindex')->assertCommandIsSuccessful();

        self::assertTrue($this->rowExists($indexableUuid), 'Published News must be indexed.');
        self::assertFalse($this->rowExists($nonIndexableUuid), 'Unpublished News must not be indexed.');
    }

    private function rowExists(string $uuid): bool
    {
        return false !== $this->connection->fetchOne(
            'SELECT id FROM timeline_feed WHERE uuid = :uuid',
            ['uuid' => $uuid],
        );
    }
}
