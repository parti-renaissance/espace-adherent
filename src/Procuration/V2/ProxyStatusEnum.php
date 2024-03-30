<?php

namespace App\Procuration\V2;

enum ProxyStatusEnum: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case EXCLUDED = 'excluded';

    public static function getAvailableStatuses(): array
    {
        return [self::PENDING, self::EXCLUDED];
    }
}
