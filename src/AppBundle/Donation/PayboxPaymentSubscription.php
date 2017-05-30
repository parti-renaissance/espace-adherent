<?php

namespace AppBundle\Donation;

final class PayboxPaymentSubscription
{
    const UNLIMITED = -1;
    const TWO_MONTHS = 2;
    const SIX_MONTHS = 6;

    const NONE = 0;

    const DURATIONS = [
        'Durée illimité' => self::UNLIMITED,
        'Pendant 2 mois' => self::TWO_MONTHS,
        'Pendant 6 mois' => self::SIX_MONTHS,
    ];

    public static function isValid(int $duration): bool
    {
        return self::NONE === $duration || in_array($duration, self::DURATIONS, true);
    }

    public static function getCommandSuffix(float $amount, int $duration): string
    {
        switch ($duration) {
            case self::UNLIMITED:
                $payments = '00';
                break;

            case self::TWO_MONTHS:
            case self::SIX_MONTHS:
                $payments = str_pad(intval($duration) - 1, 2, '0', STR_PAD_LEFT);
                break;

            default:
                // Not a subscription
                return '';
        }

        return sprintf('PBX_2MONT%sPBX_NBPAIE%sPBX_FREQ01PBX_QUAND00', str_pad($amount, 10, '0', STR_PAD_LEFT), $payments);
    }
}
