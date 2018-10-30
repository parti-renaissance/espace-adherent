<?php

namespace AppBundle\Jecoute;

use MyCLabs\Enum\Enum;

class AgeRangeEnum extends Enum
{
    public const LESS_THAN_18 = 'less_than_18';
    public const BETWEEN_19_29 = 'between_19_29';
    public const BETWEEN_30_39 = 'between_30_39';
    public const BETWEEN_40_49 = 'between_40_49';
    public const BETWEEN_50_59 = 'between_50_59';
    public const BETWEEN_60_69 = 'between_60_69';
    public const GREATER_THAN_70 = 'greater_than_70';

    public static function all(): array
    {
        return [
            self::LESS_THAN_18,
            self::BETWEEN_19_29,
            self::BETWEEN_30_39,
            self::BETWEEN_40_49,
            self::BETWEEN_50_59,
            self::BETWEEN_60_69,
            self::GREATER_THAN_70,
        ];
    }
}
