<?php

declare(strict_types=1);

namespace App\Campus;

enum RegistrationStatusEnum: string
{
    case INVITED = 'invited';
    case REGISTERED = 'registered';
    case CANCELLED = 'cancelled';

    public static function toArray(): array
    {
        return array_column(RegistrationStatusEnum::cases(), 'value');
    }
}
