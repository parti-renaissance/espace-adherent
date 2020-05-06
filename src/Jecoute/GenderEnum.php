<?php

namespace App\Jecoute;

use MyCLabs\Enum\Enum;

class GenderEnum extends Enum
{
    public const MALE = 'male';
    public const FEMALE = 'female';
    public const OTHER = 'other';

    public static function all(): array
    {
        return [
            self::MALE,
            self::FEMALE,
            self::OTHER,
        ];
    }
}
