<?php

namespace Tests\App\Test\Helper;

use PHPUnit\Framework\Assert;

class PHPUnitHelper
{
    public static function assertArraySubset(array $expected, array $actual): void
    {
        foreach ($expected as $key => $value) {
            Assert::assertArrayHasKey($key, $actual);

            if (\is_array($value)) {
                self::assertArraySubset($value, $actual[$key]);

                return;
            }

            Assert::assertSame($value, $actual[$key]);
        }
    }
}
