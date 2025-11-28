<?php

declare(strict_types=1);

namespace App\Pap;

use MyCLabs\Enum\Enum;

class BuildingEventTypeEnum extends Enum
{
    public const BUILDING = 'building';
    public const BUILDING_BLOCK = 'building_block';
    public const FLOOR = 'floor';
}
