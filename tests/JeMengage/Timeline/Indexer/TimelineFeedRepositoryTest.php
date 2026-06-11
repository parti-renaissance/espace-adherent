<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\Indexer;

use App\Entity\Timeline\TimelineFeed;
use App\Entity\Timeline\TimelineHiddenFeed;
use App\Repository\Timeline\TimelineFeedRepository;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Uid\Uuid;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
final class TimelineFeedRepositoryTest extends AbstractKernelTestCase
{
    private const string UUID_A = '11111111-1111-4111-8111-111111111111';
    private const string UUID_B = '22222222-2222-4222-8222-222222222222';
    private const string UUID_C = '33333333-3333-4333-8333-333333333333';
    private const string UUID_UNKNOWN = '99999999-9999-4999-8999-999999999999';

    private ?TimelineFeedRepository $repository = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = static::getContainer()->get(TimelineFeedRepository::class);

        // Isolate the inserted mirror rows: rolled back in tearDown, no fixture pollution.
        $this->manager->getConnection()->beginTransaction();
        foreach ([self::UUID_A, self::UUID_B, self::UUID_C] as $uuid) {
            $this->manager->persist($this->feed($uuid));
        }
        $this->manager->flush();
    }

    protected function tearDown(): void
    {
        $this->manager->getConnection()->rollBack();
        $this->repository = null;

        parent::tearDown();
    }

    public function testFindPublishableByUuidsReturnsMatchingRows(): void
    {
        $rows = $this->repository->findPublishableByUuids([self::UUID_A, self::UUID_C]);

        $found = array_map(static function (TimelineFeed $feed): string {
            return $feed->getUuid()->toRfc4122();
        }, $rows);
        sort($found);

        self::assertSame([self::UUID_A, self::UUID_C], $found);
    }

    public function testFindPublishableByUuidsIgnoresUnknownUuid(): void
    {
        $rows = $this->repository->findPublishableByUuids([self::UUID_B, self::UUID_UNKNOWN]);

        self::assertCount(1, $rows);
        self::assertSame(self::UUID_B, $rows[0]->getUuid()->toRfc4122());
    }

    public function testFindPublishableByUuidsWithEmptyArrayReturnsEmpty(): void
    {
        self::assertSame([], $this->repository->findPublishableByUuids([]));
    }

    public function testFindPublishableByUuidsExcludesHidden(): void
    {
        $this->hide(self::UUID_B);

        $rows = $this->repository->findPublishableByUuids([self::UUID_A, self::UUID_B]);

        self::assertCount(1, $rows);
        self::assertSame(self::UUID_A, $rows[0]->getUuid()->toRfc4122());
    }

    public function testFindOnePublishableByUuidReturnsRow(): void
    {
        $row = $this->repository->findOnePublishableByUuid(Uuid::fromString(self::UUID_A));

        self::assertNotNull($row);
        self::assertSame(self::UUID_A, $row->getUuid()->toRfc4122());
    }

    public function testFindOnePublishableByUuidReturnsNullWhenHidden(): void
    {
        $this->hide(self::UUID_A);

        self::assertNull($this->repository->findOnePublishableByUuid(Uuid::fromString(self::UUID_A)));
    }

    public function testFindOnePublishableByUuidReturnsNullWhenAbsent(): void
    {
        self::assertNull($this->repository->findOnePublishableByUuid(Uuid::fromString(self::UUID_UNKNOWN)));
    }

    private function hide(string $uuid): void
    {
        $this->manager->persist(new TimelineHiddenFeed(Uuid::fromString($uuid)));
        $this->manager->flush();
    }

    private function feed(string $uuid): TimelineFeed
    {
        $feed = new TimelineFeed();
        new \ReflectionProperty(TimelineFeed::class, 'uuid')->setValue($feed, Uuid::fromString($uuid));
        $feed->type = 'event';
        $feed->publicationDate = new \DateTimeImmutable('2026-05-20 10:00:00');
        $feed->display = ['objectID' => $uuid, 'type' => 'event', 'title' => 'Item '.$uuid];
        $feed->updatedAt = new \DateTimeImmutable('2026-05-20 10:00:00');

        return $feed;
    }
}
