<?php

namespace App\Adherent\Referral;

class ReferralParams
{
    public const REFERRER_CODE = 'referrer';

    public static function filterParameter(?string $parameter): ?string
    {
        if (!$parameter) {
            return null;
        }

        return mb_substr($parameter, 0, 7);
    }
}
