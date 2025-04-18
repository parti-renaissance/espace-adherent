<?php

namespace App\AppSession;

enum SessionStatusEnum: string
{
    case ACTIVE = 'active';
    case TERMINATED = 'terminated';

    public static function all(): array
    {
        return array_values(self::cases());
    }
}
