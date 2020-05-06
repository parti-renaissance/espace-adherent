<?php

namespace App\Jecoute;

use MyCLabs\Enum\Enum;

class AgeRangeEnum extends Enum
{
    public const LESS_THAN_20 = 'less_than_20';
    public const BETWEEN_20_24 = 'between_20_24';
    public const BETWEEN_25_39 = 'between_25_39';
    public const BETWEEN_40_54 = 'between_40_54';
    public const BETWEEN_55_64 = 'between_55_64';
    public const BETWEEN_65_80 = 'between_65_80';
    public const GREATER_THAN_80 = 'greater_than_80';

    public static function all(): array
    {
        return [
            self::LESS_THAN_20,
            self::BETWEEN_20_24,
            self::BETWEEN_25_39,
            self::BETWEEN_40_54,
            self::BETWEEN_55_64,
            self::BETWEEN_65_80,
            self::GREATER_THAN_80,
        ];
    }
}
