<?php

declare(strict_types=1);

namespace App\Formation\Moodle\Repair;

enum RepairStatus: string
{
    case HEALTHY = 'healthy';
    case REPAIR = 'repair';
    case MANUAL = 'manual';
}
