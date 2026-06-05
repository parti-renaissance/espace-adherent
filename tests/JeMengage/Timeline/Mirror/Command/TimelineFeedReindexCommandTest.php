<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\Mirror\Command;

use App\Entity\Jecoute\News;
use App\JeMengage\Timeline\Mirror\Message\UpsertTimelineFeedCommand;
use App\Messenger\MessageRecorder\MessageRecorderInterface;
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

    public function testReindexUpsertsCurrentItems(): void
    {
        $newsList = $this->getRepository(News::class)->findBy([], null, 2);
        self::assertCount(2, $newsList, 'Two News fixtures are required.');
        [$indexable, $nonIndexable] = $newsList;

        $indexable->setPublished(true);
        $indexable->setTitle('Reindexable News');
        $nonIndexable->setPublished(false);
        $this->manager->flush();

        // Capture the UUIDs before the command runs.
        $indexableUuid = $indexable->getUuid()->toRfc4122();
        $nonIndexableUuid = $nonIndexable->getUuid()->toRfc4122();

        $this->runCommand('app:timeline:reindex')->assertCommandIsSuccessful();

        self::assertTrue($this->rowExists($indexableUuid), 'Published News must be indexed.');
        self::assertFalse($this->rowExists($nonIndexableUuid), 'Unpublished News must not be indexed.');
    }

    public function testReindexLeavesStaleRowsForTheSeparateSweep(): void
    {
        // Going async, the reindex only re-pushes current items: the writes happen later on the
        // workers, so it can no longer sweep inline. Orphan removal is the sweep command's job.
        $this->connection->executeStatement(
            "INSERT INTO timeline_feed (uuid, type, publication_date, display, updated_at)
             VALUES ('reindex-orphan-row', 'news', '2000-01-01 00:00:00', '{}', '2000-01-01 00:00:00')",
        );

        $this->runCommand('app:timeline:reindex')->assertCommandIsSuccessful();

        self::assertGreaterThan(0, (int) $this->connection->fetchOne('SELECT COUNT(*) FROM timeline_feed'), 'At least one timeline item should be indexed.');
        self::assertTrue($this->rowExists('reindex-orphan-row'), 'The reindex no longer sweeps; the orphan row must remain.');
    }

    public function testReindexDispatchesRecentItemsFirst(): void
    {
        $newsIds = array_map(
            static fn (News $news) => $news->getId(),
            $this->getRepository(News::class)->findBy([], ['id' => 'ASC']),
        );
        self::assertGreaterThanOrEqual(2, \count($newsIds), 'At least two News fixtures are required to assert ordering.');

        $this->runCommand('app:timeline:reindex')->assertCommandIsSuccessful();

        $dispatchedNewsIds = [];
        foreach (static::getContainer()->get(MessageRecorderInterface::class)->getMessages() as $envelope) {
            $message = $envelope->getMessage();
            if ($message instanceof UpsertTimelineFeedCommand && News::class === $message->entityClass) {
                $dispatchedNewsIds[] = (int) $message->entityId;
            }
        }

        // The sync transport passes each message through the recorder twice (send + receive); collapse
        // to the logical dispatch order, which is what we assert on.
        $dispatchedNewsIds = array_values(array_unique($dispatchedNewsIds));

        $expected = $newsIds;
        rsort($expected);
        self::assertSame($expected, $dispatchedNewsIds, 'Recent News (highest id) must be dispatched first.');
    }

    private function rowExists(string $uuid): bool
    {
        return false !== $this->connection->fetchOne(
            'SELECT id FROM timeline_feed WHERE uuid = :uuid',
            ['uuid' => $uuid],
        );
    }
}
