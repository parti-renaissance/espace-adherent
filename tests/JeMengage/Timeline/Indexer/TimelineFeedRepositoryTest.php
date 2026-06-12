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
    private const string UUID_N1 = '44444444-4444-4444-8444-444444444441';
    private const string UUID_N2 = '44444444-4444-4444-8444-444444444442';
    private const string UUID_N3 = '44444444-4444-4444-8444-444444444443';
    private const string UUID_N4 = '44444444-4444-4444-8444-444444444444';
    private const string UUID_R1 = '55555555-5555-4555-8555-555555555551';

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

    public function testFindCandidateChunkReturnsNewestFirstWithKeysetCursor(): void
    {
        // Future dates: the test DB carries fixture news rows (dated around "now"), the seeded rows
        // must outrank them in the DESC ordering to make the first chunks deterministic.
        foreach ([
            self::UUID_N1 => '2027-06-01 10:00:00',
            self::UUID_N2 => '2027-06-02 10:00:00',
            self::UUID_N3 => '2027-06-03 10:00:00',
            self::UUID_N4 => '2027-06-04 10:00:00',
        ] as $uuid => $date) {
            $this->manager->persist($this->feed($uuid, 'news', $date));
        }
        $this->manager->flush();

        $first = $this->repository->findCandidateChunk(['news'], null, null, 2);
        self::assertSame([self::UUID_N4, self::UUID_N3], array_column($first, 'uuid'));

        $cursor = end($first);
        $second = $this->repository->findCandidateChunk(['news'], $cursor['publicationDate'], $cursor['id'], 2);
        self::assertSame([self::UUID_N2, self::UUID_N1], array_column($second, 'uuid'));

        // Past the last seeded row the keyset reaches the (older) fixture rows: none of ours again.
        $cursor = end($second);
        $third = $this->repository->findCandidateChunk(['news'], $cursor['publicationDate'], $cursor['id'], 50);
        self::assertSame([], array_intersect(array_column($third, 'uuid'), [self::UUID_N1, self::UUID_N2, self::UUID_N3, self::UUID_N4]));
    }

    public function testFindCandidateChunkBreaksTiesByIdDesc(): void
    {
        // Two flushes: N1 is guaranteed the lower auto-increment id.
        $this->manager->persist($this->feed(self::UUID_N1, 'news', '2027-06-01 10:00:00'));
        $this->manager->flush();
        $this->manager->persist($this->feed(self::UUID_N2, 'news', '2027-06-01 10:00:00'));
        $this->manager->flush();

        $first = $this->repository->findCandidateChunk(['news'], null, null, 1);
        self::assertSame([self::UUID_N2], array_column($first, 'uuid'));

        $cursor = $first[0];
        $second = $this->repository->findCandidateChunk(['news'], $cursor['publicationDate'], $cursor['id'], 1);
        self::assertSame([self::UUID_N1], array_column($second, 'uuid'));
    }

    public function testFindCandidateChunkExcludesHiddenAndOtherTypes(): void
    {
        $this->manager->persist($this->feed(self::UUID_N1, 'news', '2027-06-01 10:00:00'));
        $this->manager->persist($this->feed(self::UUID_N2, 'news', '2027-06-02 10:00:00'));
        $this->manager->persist($this->feed(self::UUID_R1, 'riposte', '2027-06-03 10:00:00'));
        $this->manager->flush();
        $this->hide(self::UUID_N2);

        $uuids = array_column($this->repository->findCandidateChunk(['news'], null, null, 5), 'uuid');

        self::assertContains(self::UUID_N1, $uuids);
        self::assertNotContains(self::UUID_N2, $uuids);
        self::assertNotContains(self::UUID_R1, $uuids);
    }

    public function testFindCandidateChunkReturnsArraysNotEntities(): void
    {
        $audience = ['include' => ['national' => true]];
        $this->manager->persist($this->feed(self::UUID_N1, 'news', '2027-06-01 10:00:00', $audience));
        $this->manager->flush();

        $rows = $this->repository->findCandidateChunk(['news'], null, null, 1);

        self::assertCount(1, $rows);
        $row = $rows[0];
        self::assertIsArray($row);
        self::assertEqualsCanonicalizing(['uuid', 'type', 'audience', 'publicationDate', 'id'], array_keys($row));
        self::assertSame(self::UUID_N1, $row['uuid']);
        self::assertSame('news', $row['type']);
        self::assertSame($audience, $row['audience']);
        self::assertInstanceOf(\DateTimeImmutable::class, $row['publicationDate']);
        self::assertIsInt($row['id']);
    }

    private function hide(string $uuid): void
    {
        $this->manager->persist(new TimelineHiddenFeed(Uuid::fromString($uuid)));
        $this->manager->flush();
    }

    private function feed(string $uuid, string $type = 'event', string $publicationDate = '2026-05-20 10:00:00', ?array $audience = null): TimelineFeed
    {
        $feed = new TimelineFeed();
        new \ReflectionProperty(TimelineFeed::class, 'uuid')->setValue($feed, Uuid::fromString($uuid));
        $feed->type = $type;
        $feed->publicationDate = new \DateTimeImmutable($publicationDate);
        $feed->audience = $audience;
        $feed->display = ['objectID' => $uuid, 'type' => $type, 'title' => 'Item '.$uuid];
        $feed->updatedAt = new \DateTimeImmutable($publicationDate);

        return $feed;
    }
}
