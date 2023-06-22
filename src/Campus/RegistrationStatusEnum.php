<?php

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

    public static function fromStatus(string $status): ?RegistrationStatusEnum
    {
        return match ($status) {
            'invited' => RegistrationStatusEnum::INVITED,
            'registered' => RegistrationStatusEnum::REGISTERED,
            'cancelled' => RegistrationStatusEnum::CANCELLED,
            default => null,
        };
    }
}
