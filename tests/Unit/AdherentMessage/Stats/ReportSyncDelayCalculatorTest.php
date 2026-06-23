<?php

declare(strict_types=1);

namespace Tests\App\Unit\AdherentMessage\Stats;

use App\AdherentMessage\Stats\ReportSyncDelayCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ReportSyncDelayCalculatorTest extends TestCase
{
    private const MINUTE = 60;
    private const HOUR = 3600;
    private const DAY = 86400;

    #[DataProvider('provideAgeBuckets')]
    public function testCalculateReturnsDelayForAgeBucket(int $ageSeconds, ?int $expectedMs): void
    {
        // sentAt = now - age; buckets are wide enough that test execution time is irrelevant.
        $sentAt = new \DateTimeImmutable('now')->modify(\sprintf('-%d seconds', $ageSeconds));

        self::assertSame($expectedMs, new ReportSyncDelayCalculator()->calculate($sentAt));
    }

    public static function provideAgeBuckets(): iterable
    {
        yield 'just sent (<1h) → 5 min' => [10 * self::MINUTE, 5 * self::MINUTE * 1000];
        yield '3h old (<6h) → 10 min' => [3 * self::HOUR, 10 * self::MINUTE * 1000];
        yield '1 day old (<3d) → 1 h' => [1 * self::DAY, 1 * self::HOUR * 1000];
        yield '7 days old (<14d) → 1 day' => [7 * self::DAY, 1 * self::DAY * 1000];
        yield '20 days old (>=14d) → stop' => [20 * self::DAY, null];
    }
}
