<?php

namespace App\AppSession;

enum SystemEnum: string
{
    case WEB = 'web';
    case IOS = 'iOS';
    case ANDROID = 'Android';

    public static function fromString(?string $system): self
    {
        return self::tryFrom($system) ?? self::WEB;
    }

    public static function all(): array
    {
        return array_values(self::cases());
    }
}
