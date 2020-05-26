<?php

namespace App\Donation;

use App\Extract\AbstractEmailExtractCommand;

class DonatorExtractCommand extends AbstractEmailExtractCommand
{
    public const FIELD_FIRST_NAME = 'firstName';
    public const FIELD_LAST_NAME = 'lastName';
    public const FIELD_GENDER = 'gender';
    public const FIELD_ADDRESS = 'address';
    public const FIELD_POSTAL_CODE = 'postalCode';
    public const FIELD_CITY = 'city';
    public const FIELD_COUNTRY = 'country';
    public const FIELD_NATIONALITY = 'nationality';
    public const FIELD_PHONE = 'phone';

    public static function getFieldChoices(): array
    {
        return [
            self::FIELD_GENDER,
            self::FIELD_FIRST_NAME,
            self::FIELD_LAST_NAME,
            self::FIELD_ADDRESS,
            self::FIELD_POSTAL_CODE,
            self::FIELD_CITY,
            self::FIELD_COUNTRY,
            self::FIELD_NATIONALITY,
            self::FIELD_PHONE,
        ];
    }
}
