<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\Mirror;

use App\Entity\Jecoute\News;
use Doctrine\DBAL\Connection;
use Tests\App\AbstractKernelTestCase;

/**
 * End-to-end: a flush on a timeline entity feeds the timeline_feed mirror through the real wiring
 * (decorator -> async message routed sync in test -> handler -> DBAL writer).
 */
class TimelineFeedLiveSyncTest extends AbstractKernelTestCase
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

    public function testFlushIndexesTimelineItemIntoMirror(): void
    {
        /** @var News $news */
        $news = $this->getRepository(News::class)->findOneBy([]);
        $news->setPublished(true);
        $news->setTitle('Live-synced title');
        $this->manager->flush();

        $uuid = $news->getUuid()->toRfc4122();

        $display = $this->connection->fetchOne(
            'SELECT display FROM timeline_feed WHERE uuid = :uuid',
            ['uuid' => $uuid],
        );

        self::assertNotFalse($display, 'The flushed News must have produced a mirror row.');
        self::assertSame($uuid, json_decode((string) $display, true)['objectID']);
    }

    public function testFlushRemovesMirrorRowWhenEntityBecomesNonIndexable(): void
    {
        /** @var News $news */
        $news = $this->getRepository(News::class)->findOneBy([]);

        // Force a changeset (title) so postUpdate always fires, regardless of the fixture state.
        $news->setPublished(true);
        $news->setTitle('Mirrored then removed');
        $this->manager->flush();
        $uuid = $news->getUuid()->toRfc4122();
        self::assertNotFalse($this->rowId($uuid), 'Precondition: the published News is mirrored.');

        $news->setPublished(false);
        $this->manager->flush();

        self::assertFalse($this->rowId($uuid), 'Becoming non-indexable removes the mirror row.');
    }

    private function rowId(string $objectId): mixed
    {
        return $this->connection->fetchOne(
            'SELECT id FROM timeline_feed WHERE uuid = :uuid',
            ['uuid' => $objectId],
        );
    }
}
