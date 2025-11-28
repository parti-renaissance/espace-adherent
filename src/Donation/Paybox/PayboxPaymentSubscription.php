<?php

declare(strict_types=1);

namespace App\Donation\Paybox;

final class PayboxPaymentSubscription
{
    public const NONE = 0;
    public const UNLIMITED = -1;

    public static function isValid(int $duration): bool
    {
        return self::NONE === $duration || self::UNLIMITED === $duration;
    }

    public static function getCommandSuffix(int $amount, int $duration): string
    {
        if (self::UNLIMITED === $duration) {
            return \sprintf('PBX_2MONT%sPBX_NBPAIE%sPBX_FREQ01PBX_QUAND00',
                str_pad((string) $amount, 10, '0', \STR_PAD_LEFT),
                '00'
            );
        }

        return '';
    }
}
