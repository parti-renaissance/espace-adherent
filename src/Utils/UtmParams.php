<?php

namespace App\Utils;

class UtmParams
{
    public const UTM_SOURCE = 'utm_source';
    public const UTM_CAMPAIGN = 'utm_campaign';

    public static function filterUtmParameter(?string $utmParameter): ?string
    {
        if (!$utmParameter) {
            return null;
        }

        return mb_substr($utmParameter, 0, 255);
    }
}
