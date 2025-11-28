<?php

declare(strict_types=1);

namespace App\AppSession;

enum SystemEnum: string
{
    case WEB = 'web';
    case IOS = 'ios';
    case ANDROID = 'android';

    public static function fromString(?string $system): ?self
    {
        return self::tryFrom(mb_strtolower($system));
    }

    public static function all(): array
    {
        return array_values(self::cases());
    }
}
