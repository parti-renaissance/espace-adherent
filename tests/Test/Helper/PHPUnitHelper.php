<?php

declare(strict_types=1);

namespace Tests\App\Test\Helper;

use Coduo\PHPMatcher\PHPUnit\PHPMatcherConstraint;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class PHPUnitHelper
{
    public static function assertArraySubset(array $expected, array $actual): void
    {
        foreach ($expected as $key => $value) {
            Assert::assertArrayHasKey($key, $actual);

            if (\is_array($value)) {
                self::assertArraySubset($value, $actual[$key]);

                continue;
            }

            TestCase::assertThat(
                \is_array($actual[$key]) ? $actual[$key] : self::getValueAsString($actual[$key]),
                new PHPMatcherConstraint(self::getValueAsString($value))
            );
        }
    }

    private static function getValueAsString($value): string
    {
        return \is_bool($value) ? json_encode($value) : (string) $value;
    }
}
