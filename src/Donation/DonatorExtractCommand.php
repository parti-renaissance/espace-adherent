<?php

namespace App\Donation;

use App\Extract\AbstractEmailExtractCommand;

class DonatorExtractCommand extends AbstractEmailExtractCommand
{
    public const FIELD_FIRST_NAME = 'firstName';
    public const FIELD_LAST_NAME = 'lastName';
    public const FIELD_GENDER = 'gender';
    public const FIELD_CITY = 'city';
    public const FIELD_COUNTRY = 'country';

    public static function getFieldChoices(): array
    {
        return [
            self::FIELD_FIRST_NAME,
            self::FIELD_LAST_NAME,
            self::FIELD_GENDER,
            self::FIELD_CITY,
            self::FIELD_COUNTRY,
        ];
    }
}
