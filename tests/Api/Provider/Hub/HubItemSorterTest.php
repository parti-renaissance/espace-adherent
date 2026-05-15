<?php

declare(strict_types=1);

namespace Tests\App\Api\Provider\Hub;

use App\Api\Provider\Hub\HubItemRow;
use App\Api\Provider\Hub\HubItemSorter;
use App\Entity\Event\Event;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class HubItemSorterTest extends TestCase
{
    private HubItemSorter $sorter;

    protected function setUp(): void
    {
        $this->sorter = new HubItemSorter();
    }

    public function testCompareWithUserCoordsOrdersByDistanceThenByBeginAt(): void
    {
        $closer = $this->row(distance: 1.5, beginAt: '2026-06-01 10:00:00');
        $farther = $this->row(distance: 4.0, beginAt: '2026-05-01 10:00:00');

        self::assertLessThan(0, $this->sorter->compare($closer, $farther, hasUserCoords: true));
        self::assertGreaterThan(0, $this->sorter->compare($farther, $closer, hasUserCoords: true));

        $sameDistanceEarlier = $this->row(distance: 2.0, beginAt: '2026-05-01 10:00:00');
        $sameDistanceLater = $this->row(distance: 2.0, beginAt: '2026-06-01 10:00:00');

        self::assertLessThan(0, $this->sorter->compare($sameDistanceEarlier, $sameDistanceLater, hasUserCoords: true));
    }

    public function testCompareWithoutUserCoordsOrdersByPriorityThenByTimeToBegin(): void
    {
        $upcomingSoon = $this->row(priority: 1, timeToBegin: 3600);
        $upcomingLater = $this->row(priority: 1, timeToBegin: 86400);
        $past = $this->row(priority: 0, timeToBegin: 1000);

        self::assertLessThan(0, $this->sorter->compare($upcomingSoon, $past, hasUserCoords: false));
        self::assertGreaterThan(0, $this->sorter->compare($past, $upcomingSoon, hasUserCoords: false));
        self::assertLessThan(0, $this->sorter->compare($upcomingSoon, $upcomingLater, hasUserCoords: false));
    }

    public function testCompareWithUserCoordsFallsBackWhenDistanceIsNull(): void
    {
        $withDistance = $this->row(distance: 5.0);
        $withoutDistance = $this->row(distance: null);

        self::assertLessThan(0, $this->sorter->compare($withDistance, $withoutDistance, hasUserCoords: true));
    }

    public function testCompareIsStableOnUuid(): void
    {
        $uuidLow = Uuid::fromString('00000000-0000-0000-0000-000000000001');
        $uuidHigh = Uuid::fromString('00000000-0000-0000-0000-000000000002');

        $a = $this->row(priority: 1, timeToBegin: 100, uuid: $uuidLow);
        $b = $this->row(priority: 1, timeToBegin: 100, uuid: $uuidHigh);

        self::assertLessThan(0, $this->sorter->compare($a, $b, hasUserCoords: false));
    }

    private function row(
        int $priority = 1,
        int $timeToBegin = 1000,
        ?float $distance = null,
        string $beginAt = '2026-06-01 10:00:00',
        ?Uuid $uuid = null,
        ?string $createdAt = null,
        ?string $finishAt = null,
        int $participantsCount = 0,
    ): HubItemRow {
        $uuid ??= Uuid::v4();
        $entity = $this->createStub(Event::class);
        $entity->method('getUuid')->willReturn($uuid);

        return new HubItemRow(
            entity: $entity,
            type: HubItemRow::TYPE_EVENT,
            priority: $priority,
            timeToBegin: $timeToBegin,
            distance: $distance,
            beginAt: new \DateTimeImmutable($beginAt),
            createdAt: new \DateTimeImmutable($createdAt ?? $beginAt),
            finishAt: $finishAt ? new \DateTimeImmutable($finishAt) : null,
            participantsCount: $participantsCount,
        );
    }
}
