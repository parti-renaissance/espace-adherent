<?php

namespace App\Membership;

use MyCLabs\Enum\Enum;

final class MandatesEnum extends Enum
{
    private const REGIONAL_COUNCILOR = 'regional_councilor';
    private const DEPARTMENTAL_COUNCILOR = 'departmental_councilor';
    private const CONSULAR_COUNSELOR = 'consular_conselor';
    private const CORSICA_ASSEMBLY_MEMBER = 'corsica_assembly_member';
    private const MAYOR = 'mayor';
    private const MAYOR_ASSISTANT = 'mayor_assistant';
    private const CITY_COUNCILOR = 'city_councilor';
    private const BOROUGH_COUNCILOR = 'borough_councilor';
    private const PARLIAMENTARY = 'parliamentary';
    private const DEPUTY = 'deputy';
    private const SENATOR = 'senator';
    private const EUROPEAN_DEPUTY = 'european_deputy';
    private const PRESIDENT_OF_EPCI = 'president_of_epci';
    private const VICE_PRESIDENT_OF_EPCI = 'vice_president_of_epci';
    private const EPCI_MEMBER = 'epci_member';

    public const ALL = [
        self::REGIONAL_COUNCILOR,
        self::DEPARTMENTAL_COUNCILOR,
        self::CONSULAR_COUNSELOR,
        self::CORSICA_ASSEMBLY_MEMBER,
        self::MAYOR,
        self::MAYOR_ASSISTANT,
        self::CITY_COUNCILOR,
        self::BOROUGH_COUNCILOR,
        self::PARLIAMENTARY,
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
        'adherent.mandate.consular_conselor' => self::CONSULAR_COUNSELOR,
        'adherent.mandate.corsica_assembly_member' => self::CORSICA_ASSEMBLY_MEMBER,
        'adherent.mandate.mayor' => self::MAYOR,
        'adherent.mandate.mayor_assistant' => self::MAYOR_ASSISTANT,
        'adherent.mandate.city_councilor' => self::CITY_COUNCILOR,
        'adherent.mandate.borough_councilor' => self::BOROUGH_COUNCILOR,
        'adherent.mandate.parliamentary' => self::PARLIAMENTARY,
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
