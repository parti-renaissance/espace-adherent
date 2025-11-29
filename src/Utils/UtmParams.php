<?php

declare(strict_types=1);

namespace App\Utils;

use Symfony\Component\HttpFoundation\Request;

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

    public static function mergeParams(array $initialParams, ?string $utmSource, ?string $utmCampaign): array
    {
        return array_merge($initialParams, array_filter([
            self::UTM_SOURCE => $utmSource,
            self::UTM_CAMPAIGN => $utmCampaign,
        ]));
    }

    public static function fromRequest(Request $request): array
    {
        return array_filter([
            self::UTM_SOURCE => self::filterUtmParameter($request->query->get(self::UTM_SOURCE)),
            self::UTM_CAMPAIGN => self::filterUtmParameter($request->query->get(self::UTM_CAMPAIGN)),
        ]);
    }
}
