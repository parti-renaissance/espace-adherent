<?php

namespace App\Procuration\V2;

enum RequestStatusEnum: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case MANUAL = 'manual';
    case EXCLUDED = 'excluded';

    public static function getAvailableStatuses(): array
    {
        return [self::PENDING, self::MANUAL, self::EXCLUDED];
    }
}
