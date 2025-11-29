<?php

declare(strict_types=1);

namespace App;

use MyCLabs\Enum\Enum;

final class AppCodeEnum extends Enum
{
    public const PLATFORM = 'platform';
    public const RENAISSANCE = 'renaissance';
    public const BESOIN_D_EUROPE = 'besoindeurope';
    public const LEGISLATIVE = 'legislative';
    public const VOX = 'vox';
    public const JEMENGAGE_WEB = 'jemengage_web';
    public const JEMENGAGE_MOBILE = 'jemengage_mobile';
    public const FORMATION = 'formation';
    public const EAGGLE = 'eaggle';

    public static function isRenaissanceApp(?string $code): bool
    {
        return self::RENAISSANCE === $code;
    }

    public static function isMobileApp(?string $appCode): bool
    {
        return \in_array($appCode, [self::BESOIN_D_EUROPE, self::LEGISLATIVE, self::VOX]);
    }
}
