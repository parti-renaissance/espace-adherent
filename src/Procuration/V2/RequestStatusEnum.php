<?php

declare(strict_types=1);

namespace App\Procuration\V2;

enum RequestStatusEnum: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case MANUAL = 'manual';
    case EXCLUDED = 'excluded';
    case DUPLICATE = 'duplicate';

    public static function getAvailableStatuses(): array
    {
        return [self::PENDING, self::MANUAL, self::EXCLUDED];
    }
}
