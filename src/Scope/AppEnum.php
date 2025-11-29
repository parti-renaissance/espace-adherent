<?php

declare(strict_types=1);

namespace App\Scope;

use MyCLabs\Enum\Enum;

class AppEnum extends Enum
{
    public const DATA_CORNER = 'data_corner';
    public const JEMARCHE = 'jemarche';

    public const ALL = [
        self::DATA_CORNER,
        self::JEMARCHE,
    ];
}
