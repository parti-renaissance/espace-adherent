<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Campaign\Audience;

use App\Mailchimp\Campaign\Audience\AudienceCheckCalculator;
use App\Mailchimp\Campaign\Audience\AudienceCheckEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AudienceCheckCalculatorTest extends TestCase
{
    #[DataProvider('provideCases')]
    public function testCompute(int $expected, int $prepared, AudienceCheckEnum $expectedResult): void
    {
        $calculator = new AudienceCheckCalculator();

        self::assertSame($expectedResult, $calculator->compute($expected, $prepared));
    }

    /**
     * @return iterable<string, array{int, int, AudienceCheckEnum}>
     */
    public static function provideCases(): iterable
    {
        // Tier ≤ 2k : MATCH ≤ 0.5%, DRIFT ≤ 2%, MISMATCH > 2%
        yield 'tier 1k — perfect match' => [1000, 1000, AudienceCheckEnum::Match];
        yield 'tier 1k — 0.2% drift = match' => [1000, 998, AudienceCheckEnum::Match];
        yield 'tier 1k — 1.5% drift' => [1000, 985, AudienceCheckEnum::Drift];
        yield 'tier 1k — 5% mismatch' => [1000, 950, AudienceCheckEnum::Mismatch];

        // Tier 2k-10k : MATCH ≤ 5%, DRIFT ≤ 8%, MISMATCH > 8%
        yield 'tier 5k — 5% match' => [5000, 4750, AudienceCheckEnum::Match];
        yield 'tier 5k — 8% drift' => [5000, 4600, AudienceCheckEnum::Drift];
        yield 'tier 5k — 12% mismatch' => [5000, 4400, AudienceCheckEnum::Mismatch];

        // Tier > 10k : MATCH ≤ 10%, DRIFT ≤ 15%, MISMATCH > 15%
        yield 'tier 100k — 8% match' => [100_000, 92_000, AudienceCheckEnum::Match];
        yield 'tier 100k — 12% drift' => [100_000, 88_000, AudienceCheckEnum::Drift];
        yield 'tier 100k — 20% mismatch' => [100_000, 80_000, AudienceCheckEnum::Mismatch];

        // Degenerate cases
        yield 'expected 0 — mismatch' => [0, 0, AudienceCheckEnum::Mismatch];

        // prepared > expected (inverse case, theoretical)
        yield 'tier 1k — 0.2% over (prepared > expected)' => [1000, 1002, AudienceCheckEnum::Match];
    }
}
