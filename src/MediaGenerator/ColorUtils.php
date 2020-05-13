<?php

namespace App\MediaGenerator;

abstract class ColorUtils
{
    public static function hex2RGBA(string $hexColor, float $opacity = 1.0): array
    {
        $int = \hexdec(ltrim($hexColor, '#'));

        return [
            0xFF & ($int >> 0x10),
            0xFF & ($int >> 0x8),
            0xFF & $int,
            $opacity,
        ];
    }

    public static function hex2RGBAAsString(string $hexColor, float $opacity = 1.0): string
    {
        return sprintf('rgba(%s)', implode(', ', static::hex2RGBA($hexColor, $opacity)));
    }
}
