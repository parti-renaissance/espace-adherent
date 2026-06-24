<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\Mirror;

use App\Entity\Committee;
use App\Entity\Event\Event;
use App\Entity\Jecoute\News;
use App\Event\EventVisibilityEnum;
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
        // Isolate the fixture mutations (and the mirror rows they trigger) behind a rolled-back
        // transaction: the writer uses this same connection, so the rollback undoes both.
        $this->connection->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->connection->rollBack();
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

    public function testFlushPopulatesDenormalizedColumnsForCommitteeEvent(): void
    {
        $event = $this->firstIndexableEvent();
        $committee = $this->getRepository(Committee::class)->findOneBy([]);
        self::assertNotNull($committee, 'Fixtures must contain at least one committee.');

        $event->setCommittee($committee);
        $event->agora = null;
        $event->visibility = EventVisibilityEnum::PUBLIC;
        $event->setName('Live-synced committee event');
        $this->manager->flush();

        $row = $this->denormalizedColumns($event->getUuid()->toRfc4122());
        self::assertSame('public', $row['visibility']);
        self::assertSame($committee->getUuidAsString(), $row['committee_uuid']);
        self::assertNull($row['agora_uuid']);
    }

    public function testFlushLeavesCommitteeColumnNullForPublicNonCommitteeEvent(): void
    {
        $event = $this->firstIndexableEvent();

        $event->setCommittee(null);
        $event->agora = null;
        $event->visibility = EventVisibilityEnum::PUBLIC;
        $event->setName('Live-synced public event');
        $this->manager->flush();

        $row = $this->denormalizedColumns($event->getUuid()->toRfc4122());
        self::assertSame('public', $row['visibility']);
        self::assertNull($row['committee_uuid']);
        self::assertNull($row['agora_uuid']);
    }

    private function firstIndexableEvent(): Event
    {
        foreach ($this->getRepository(Event::class)->findBy(['published' => true]) as $event) {
            if ($event->isIndexable()) {
                return $event;
            }
        }

        self::fail('Fixtures must contain at least one indexable event.');
    }

    /**
     * @return array<string, mixed>
     */
    private function denormalizedColumns(string $uuid): array
    {
        return $this->connection->fetchAssociative(
            'SELECT visibility, committee_uuid, agora_uuid FROM timeline_feed WHERE uuid = :uuid',
            ['uuid' => $uuid],
        );
    }

    private function rowId(string $objectId): mixed
    {
        return $this->connection->fetchOne(
            'SELECT id FROM timeline_feed WHERE uuid = :uuid',
            ['uuid' => $objectId],
        );
    }
}
