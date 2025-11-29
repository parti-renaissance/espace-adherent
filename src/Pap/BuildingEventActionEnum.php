<?php

declare(strict_types=1);

namespace App\Pap;

use MyCLabs\Enum\Enum;

class BuildingEventActionEnum extends Enum
{
    public const OPEN = 'open';
    public const CLOSE = 'close';
}
