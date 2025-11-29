<?php

declare(strict_types=1);

namespace App\Utils;

use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class PhoneNumberUtils
{
    public static function format(?PhoneNumber $phone, int $format = PhoneNumberFormat::INTERNATIONAL): string
    {
        if (!$phone) {
            return '';
        }

        return PhoneNumberUtil::getInstance()->format($phone, $format);
    }

    public static function create(string $number, ?string $region = null): ?PhoneNumber
    {
        return PhoneNumberUtil::getInstance()->parse($number, $region);
    }
}
