<?php

namespace App\Team;

use MyCLabs\Enum\Enum;

class TypeEnum extends Enum
{
    public const PHONING = 'phoning';

    public const ALL = [
        self::PHONING,
    ];
}
