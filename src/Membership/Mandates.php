<?php

namespace App\Membership;

use MyCLabs\Enum\Enum;

final class Mandates extends Enum
{
    private const REGIONAL_COUNCILOR = 'regional_councilor';
    private const DEPARTMENTAL_COUNCILOR = 'departmental_councilor';
    private const MAYOR = 'mayor';
    private const CITY_COUNCILOR = 'city_councilor';
    private const PARLIAMENTARY = 'parliamentary';
    private const EUROPEAN_DEPUTY = 'european_deputy';
    private const CONSULAR_COUNSELOR = 'consular_conselor';

    public const ALL = [
        self::REGIONAL_COUNCILOR,
        self::DEPARTMENTAL_COUNCILOR,
        self::MAYOR,
        self::CITY_COUNCILOR,
        self::PARLIAMENTARY,
        self::EUROPEAN_DEPUTY,
        self::CONSULAR_COUNSELOR,
    ];

    public const CHOICES = [
        'adherent.mandate.regional_councilor' => self::REGIONAL_COUNCILOR,
        'adherent.mandate.departmental_councilor' => self::DEPARTMENTAL_COUNCILOR,
        'adherent.mandate.mayor' => self::MAYOR,
        'adherent.mandate.city_councilor' => self::CITY_COUNCILOR,
        'adherent.mandate.parliamentary' => self::PARLIAMENTARY,
        'adherent.mandate.european_deputy' => self::EUROPEAN_DEPUTY,
        'adherent.mandate.consular_conselor' => self::CONSULAR_COUNSELOR,
    ];

    public static function all(): array
    {
        return self::ALL;
    }
}
