<?php

namespace App\Utils;

use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class PhoneNumberFormatter
{
    public static function format(?PhoneNumber $phone, int $format = PhoneNumberFormat::INTERNATIONAL): string
    {
        if (!$phone) {
            return '';
        }

        $phoneUtil = PhoneNumberUtil::getInstance();

        return $phone ? $phoneUtil->format($phone, $format) : '';
    }
}
