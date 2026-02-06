<?php

declare(strict_types=1);

namespace App\Procuration;

enum ProxyStatusEnum: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case EXCLUDED = 'excluded';
    case DUPLICATE = 'duplicate';

    public static function getAvailableStatuses(): array
    {
        return [self::PENDING, self::EXCLUDED];
    }
}
