<?php

namespace App\Adherent\Tag;

use MyCLabs\Enum\Enum;

class TagFilterEnum extends Enum
{
    public const UNDECLARED_REVENUE = 'undeclared_revenue';
    public const COTISATION_OK = 'cotisation_ok';
    public const COTISATION_EXEMPT = 'cotisation_exempt';
    public const COTISATION_ELIGIBLE = 'cotisation_eligible';
    public const COTISATION_NOT_ELIGIBLE = 'cotisation_not_eligible';
    public const COTISATION_NOK = 'cotisation_nok';

    public static function getFiltersTagsMapping(): array
    {
        return [
            self::UNDECLARED_REVENUE => [TagEnum::ELU_ATTENTE_DECLARATION => false],
            self::COTISATION_OK => [TagEnum::ELU_COTISATION_OK => true],
            self::COTISATION_EXEMPT => [TagEnum::ELU_COTISATION_OK => true, TagEnum::ELU_COTISATION_OK_EXEMPTE => true],
            self::COTISATION_ELIGIBLE => [TagEnum::ELU_COTISATION_OK => true, TagEnum::ELU_COTISATION_OK_NON_SOUMIS => true],
            self::COTISATION_NOT_ELIGIBLE => [TagEnum::ELU_COTISATION_OK => true, TagEnum::ELU_COTISATION_OK_NON_SOUMIS => false],
            self::COTISATION_NOK => [TagEnum::ELU_COTISATION_OK => false],
        ];
    }

    public static function getFiltersTags(): array
    {
        return array_keys(static::getFiltersTagsMapping());
    }
}
