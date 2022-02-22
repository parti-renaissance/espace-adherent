<?php

namespace App;

use MyCLabs\Enum\Enum;

final class AppCodeEnum extends Enum
{
    public const PLATFORM = 'platform';
    public const COALITIONS = 'coalitions';
    public const JEMENGAGE_WEB = 'jemengage_web';
    public const JEMENGAGE_MOBILE = 'jemengage_mobile';

    public static function getJemangageAppCodes(): array
    {
        return [
            self::JEMENGAGE_WEB,
            self::JEMENGAGE_MOBILE,
        ];
    }

    public static function isJeMengage(?string $code): bool
    {
        return \in_array($code, static::getJemangageAppCodes(), true);
    }

    public static function isCoalitionsApp(?string $code): bool
    {
        return self::COALITIONS === $code;
    }
}
