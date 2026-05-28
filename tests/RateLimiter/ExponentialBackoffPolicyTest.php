<?php

declare(strict_types=1);

namespace Tests\App\RateLimiter;

use App\RateLimiter\ExponentialBackoffPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ExponentialBackoffPolicyTest extends TestCase
{
    public function testIsThrottledReturnsFalseWhenAttemptCountIsZero(): void
    {
        $policy = new ExponentialBackoffPolicy(30, 3600);

        self::assertFalse($policy->isThrottled(0, new \DateTimeImmutable()));
    }

    #[DataProvider('provideExponentialCurve')]
    public function testExponentialCurveMatchesFormula(int $count, int $expectedDelaySeconds): void
    {
        $policy = new ExponentialBackoffPolicy(30, 3600);
        $lastAttempt = new \DateTimeImmutable('@'.(time() - ($expectedDelaySeconds - 1)));

        self::assertTrue(
            $policy->isThrottled($count, $lastAttempt),
            "Attempt #{$count}: must still be throttled 1 second before the delay window ends.",
        );

        $lastAttempt = new \DateTimeImmutable('@'.(time() - ($expectedDelaySeconds + 1)));

        self::assertFalse(
            $policy->isThrottled($count, $lastAttempt),
            "Attempt #{$count}: must NOT be throttled 1 second past the delay window.",
        );
    }

    public static function provideExponentialCurve(): iterable
    {
        yield '1st attempt → 30s' => [1, 30];
        yield '2nd attempt → 60s' => [2, 60];
        yield '3rd attempt → 120s' => [3, 120];
        yield '4th attempt → 240s' => [4, 240];
        yield '5th attempt → 480s' => [5, 480];
        yield '6th attempt → 960s' => [6, 960];
        yield '7th attempt → 1920s' => [7, 1920];
        yield '8th attempt → 3600s (capped)' => [8, 3600];
        yield '20th attempt → 3600s (capped)' => [20, 3600];
    }

    #[DataProvider('provideInvalidConfigs')]
    public function testConstructorRejectsInvalidConfig(int $base, int $max, int $multiplier): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ExponentialBackoffPolicy($base, $max, $multiplier);
    }

    public static function provideInvalidConfigs(): iterable
    {
        yield 'base < 1' => [0, 3600, 2];
        yield 'max < base' => [60, 30, 2];
        yield 'multiplier < 1' => [30, 3600, 0];
    }
}
