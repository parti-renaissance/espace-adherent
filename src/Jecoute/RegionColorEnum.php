<?php

declare(strict_types=1);

namespace App\Jecoute;

use MyCLabs\Enum\Enum;

class RegionColorEnum extends Enum
{
    public const BLUE = 'blue';
    public const GREEN = 'green';
    public const RED = 'red';
    public const ORANGE = 'orange';
    public const PURPLE = 'purple';
    public const YELLOW = 'yellow';
    public const PINK = 'pink';

    public static function all(): array
    {
        return [
            self::BLUE,
            self::GREEN,
            self::RED,
            self::ORANGE,
            self::PURPLE,
            self::YELLOW,
            self::PINK,
        ];
    }
}
