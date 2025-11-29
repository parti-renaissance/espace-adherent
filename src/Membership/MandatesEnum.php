<?php

declare(strict_types=1);

namespace App\Membership;

use MyCLabs\Enum\Enum;

final class MandatesEnum extends Enum
{
    public const REGIONAL_COUNCILOR = 'regional_councilor';
    public const DEPARTMENTAL_COUNCILOR = 'departmental_councilor';
    public const CONSULAR_COUNCILOR = 'consular_councilor';
    public const CORSICA_ASSEMBLY_MEMBER = 'corsica_assembly_member';
    public const MAYOR = 'mayor';
    public const MAYOR_ASSISTANT = 'mayor_assistant';
    public const CITY_COUNCILOR = 'city_councilor';
    public const BOROUGH_COUNCILOR = 'borough_councilor';
    public const DEPUTY = 'deputy';
    public const SENATOR = 'senator';
    public const EUROPEAN_DEPUTY = 'european_deputy';
    public const PRESIDENT_OF_EPCI = 'president_of_epci';
    public const VICE_PRESIDENT_OF_EPCI = 'vice_president_of_epci';
    public const EPCI_MEMBER = 'epci_member';

    public const ALL = [
        self::REGIONAL_COUNCILOR,
        self::DEPARTMENTAL_COUNCILOR,
        self::CONSULAR_COUNCILOR,
        self::CORSICA_ASSEMBLY_MEMBER,
        self::MAYOR,
        self::MAYOR_ASSISTANT,
        self::CITY_COUNCILOR,
        self::BOROUGH_COUNCILOR,
        self::DEPUTY,
        self::SENATOR,
        self::EUROPEAN_DEPUTY,
        self::PRESIDENT_OF_EPCI,
        self::VICE_PRESIDENT_OF_EPCI,
        self::EPCI_MEMBER,
    ];

    public const CHOICES = [
        'adherent.mandate.regional_councilor' => self::REGIONAL_COUNCILOR,
        'adherent.mandate.departmental_councilor' => self::DEPARTMENTAL_COUNCILOR,
        'adherent.mandate.consular_councilor' => self::CONSULAR_COUNCILOR,
        'adherent.mandate.corsica_assembly_member' => self::CORSICA_ASSEMBLY_MEMBER,
        'adherent.mandate.mayor' => self::MAYOR,
        'adherent.mandate.mayor_assistant' => self::MAYOR_ASSISTANT,
        'adherent.mandate.city_councilor' => self::CITY_COUNCILOR,
        'adherent.mandate.borough_councilor' => self::BOROUGH_COUNCILOR,
        'adherent.mandate.deputy' => self::DEPUTY,
        'adherent.mandate.senator' => self::SENATOR,
        'adherent.mandate.european_deputy' => self::EUROPEAN_DEPUTY,
        'adherent.mandate.president_of_epci' => self::PRESIDENT_OF_EPCI,
        'adherent.mandate.vice_president_of_epci' => self::VICE_PRESIDENT_OF_EPCI,
        'adherent.mandate.epci_member' => self::EPCI_MEMBER,
    ];

    public static function all(): array
    {
        return self::ALL;
    }
}
