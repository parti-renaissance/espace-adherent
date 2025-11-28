<?php

declare(strict_types=1);

namespace App\Adherent;

use App\Extract\AbstractEmailExtractCommand;

class AdherentExtractCommand extends AbstractEmailExtractCommand
{
    public const FIELD_FIRST_NAME = 'firstName';
    public const FIELD_LAST_NAME = 'lastName';
    public const FIELD_GENDER = 'gender';
    public const FIELD_BIRTH_DATE = 'birthDate';
    public const FIELD_NATIONALITY = 'nationality';
    public const FIELD_PHONE = 'phone';
    public const FIELD_REGISTERED_AT = 'registeredAt';
    public const FIELD_COUNTRY = 'country';
    public const FIELD_POSTAL_CODE = 'postalCode';
    public const FIELD_CITY = 'city';
    public const FIELD_ADDRESS = 'address';
    public const FIELD_ADDITIONAL_ADDRESS = 'additionalAddress';
    public const FIELD_SOURCE = 'source';

    public static function getFieldChoices(): array
    {
        return [
            self::FIELD_GENDER,
            self::FIELD_FIRST_NAME,
            self::FIELD_LAST_NAME,
            self::FIELD_ADDRESS,
            self::FIELD_ADDITIONAL_ADDRESS,
            self::FIELD_POSTAL_CODE,
            self::FIELD_CITY,
            self::FIELD_COUNTRY,
            self::FIELD_NATIONALITY,
            self::FIELD_PHONE,
            self::FIELD_REGISTERED_AT,
            self::FIELD_BIRTH_DATE,
            self::FIELD_SOURCE,
        ];
    }
}
