<?php

namespace App;

use MyCLabs\Enum\Enum;

final class AppCodeEnum extends Enum
{
    public const PLATFORM = 'platform';
    public const RENAISSANCE = 'renaissance';
    public const BESOIN_D_EUROPE = 'besoindeurope';
    public const JEMENGAGE = 'jemengage';
    public const JEMENGAGE_WEB = 'jemengage_web';
    public const JEMENGAGE_MOBILE = 'jemengage_mobile';
    public const VOX = 'vox';

    public static function getJemangageAppCodes(): array
    {
        return [
            self::JEMENGAGE,
            self::JEMENGAGE_WEB,
            self::JEMENGAGE_MOBILE,
        ];
    }

    public static function isJeMengage(?string $code): bool
    {
        return \in_array($code, static::getJemangageAppCodes(), true);
    }

    public static function isRenaissanceApp(?string $code): bool
    {
        return self::RENAISSANCE === $code;
    }
}
