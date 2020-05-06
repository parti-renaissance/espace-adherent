<?php

namespace Tests\App\MediaGenerator;

use App\MediaGenerator\ColorUtils;
use PHPUnit\Framework\TestCase;

class ColorUtilsTest extends TestCase
{
    /**
     * @dataProvider getColors
     */
    public function testHex2RGBA(string $hexColor, float $opacity, array $expectedColorAsArray): void
    {
        $this->assertSame($expectedColorAsArray, ColorUtils::hex2RGBA($hexColor, $opacity));
    }

    /**
     * @dataProvider getColors
     */
    public function testHex2RGBAAsString(string $hexColor, float $opacity, array $expectedColorAsArray): void
    {
        $this->assertSame(
            sprintf('rgba(%s)', implode(', ', $expectedColorAsArray)),
            ColorUtils::hex2RGBAAsString($hexColor, $opacity)
        );
    }

    public function getColors(): array
    {
        return [
            ['ff0000', .5, [255, 0, 0, .5]],
            ['ffff00', .0, [255, 255, 0, .0]],
            ['#ffffff', 1.0, [255, 255, 255, 1.0]],
        ];
    }
}
