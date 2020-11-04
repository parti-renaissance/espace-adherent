<?php

namespace App\Entity\ElectedRepresentative;

use App\Entity\Geo\Zone;
use MyCLabs\Enum\Enum;

final class MandateTypeEnum extends Enum
{
    public const CITY_COUNCIL = 'conseiller_municipal';
    public const EPCI_MEMBER = 'membre_EPCI';
    public const DEPARTMENTAL_COUNCIL = 'conseiller_departemental';
    public const REGIONAL_COUNCIL = 'conseiller_regional';
    public const CORSICA_ASSEMBLY_MEMBER = 'membre_assemblee_corse';
    public const DEPUTY = 'depute';
    public const SENATOR = 'senateur';
    public const EURO_DEPUTY = 'euro_depute';
    public const BOROUGH_COUNCIL = 'conseiller_d_arrondissement';
    public const CONSULAR_COUNCIL = 'conseiller_consulaire';

    public const CITY_COUNCIL_LABEL = 'Conseiller(e) municipal(e)';
    public const EPCI_MEMBER_LABEL = 'Membre d\'EPCI';
    public const DEPARTMENTAL_COUNCIL_LABEL = 'Conseiller(e) départemental(e)';
    public const REGIONAL_COUNCIL_LABEL = 'Conseiller(e) régional(e)';
    public const CORSICA_ASSEMBLY_MEMBER_LABEL = 'Membre de l\'Assemblée de Corse';
    public const DEPUTY_LABEL = 'Député(e)';
    public const SENATOR_LABEL = 'Sénateur(rice)';
    public const EURO_DEPUTY_LABEL = 'Député(e) européen(ne)';
    public const BOROUGH_COUNCIL_LABEL = 'Conseiller(ère) d\'arrondissement';
    public const CONSULAR_COUNCIL_LABEL = 'Conseiller(ère) FDE';

    public const CHOICES = [
        self::CITY_COUNCIL_LABEL => self::CITY_COUNCIL,
        self::EPCI_MEMBER_LABEL => self::EPCI_MEMBER,
        self::DEPARTMENTAL_COUNCIL_LABEL => self::DEPARTMENTAL_COUNCIL,
        self::REGIONAL_COUNCIL_LABEL => self::REGIONAL_COUNCIL,
        self::CORSICA_ASSEMBLY_MEMBER_LABEL => self::CORSICA_ASSEMBLY_MEMBER,
        self::DEPUTY_LABEL => self::DEPUTY,
        self::SENATOR_LABEL => self::SENATOR,
        self::EURO_DEPUTY_LABEL => self::EURO_DEPUTY,
        self::BOROUGH_COUNCIL_LABEL => self::BOROUGH_COUNCIL,
        self::CONSULAR_COUNCIL_LABEL => self::CONSULAR_COUNCIL,
    ];

    public const ZONES_BY_MANDATE = [
        self::CITY_COUNCIL => [
            Zone::CITY,
            Zone::BOROUGH,
        ],
        self::EPCI_MEMBER => [
            Zone::CITY_COMMUNITY,
        ],
        self::DEPARTMENTAL_COUNCIL => [
            Zone::DEPARTMENT,
        ],
        self::REGIONAL_COUNCIL => [
            Zone::REGION,
            Zone::DEPARTMENT,
        ],
        self::CORSICA_ASSEMBLY_MEMBER => [],
        self::DEPUTY => [
            Zone::DISTRICT,
        ],
        self::SENATOR => [
            Zone::DISTRICT,
            Zone::DEPARTMENT,
            Zone::FOREIGN_DISTRICT,
        ],
        self::CONSULAR_COUNCIL => [
            Zone::CONSULAR_DISTRICT,
        ],
        self::EURO_DEPUTY => [
            Zone::CITY,
            Zone::BOROUGH,
            Zone::DEPARTMENT,
            Zone::DISTRICT,
        ],
        self::BOROUGH_COUNCIL => [
            Zone::CITY,
            Zone::BOROUGH,
        ],
    ];
}
