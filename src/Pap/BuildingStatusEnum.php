<?php

declare(strict_types=1);

namespace App\Pap;

use MyCLabs\Enum\Enum;

class BuildingStatusEnum extends Enum
{
    public const TODO = 'todo';
    public const ONGOING = 'ongoing';
    public const COMPLETED = 'completed';

    public const COMPLETED_HYBRID = 'completed_hybrid';
    public const COMPLETED_PAP = 'completed_pap';
    public const COMPLETED_BOITAGE = 'completed_boitage';
}
