<?php

declare(strict_types=1);

namespace App\Entity\ElectedRepresentative;

use App\Entity\Geo\Zone;
use MyCLabs\Enum\Enum;

final class MandateTypeEnum extends Enum
{
    private const CORSICA_REGION_CODE = '94';

    public const TYPE_LOCAL = 'local_mandate';
    public const TYPE_NATIONAL = 'national_mandate';
    public const TYPE_ALL = 'all';
    public const TYPE_NONE = 'none';

    public const TYPE_LOCAL_LABEL = 'Mandat local';
    public const TYPE_NATIONAL_LABEL = 'Mandat national';
    public const TYPE_ALL_LABEL = 'Tous';
    public const TYPE_NONE_LABEL = 'Aucun';

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

    public const TYPE_CHOICES = [
        self::TYPE_LOCAL_LABEL => self::TYPE_LOCAL,
        self::TYPE_NATIONAL_LABEL => self::TYPE_NATIONAL,
    ];

    public const TYPE_CHOICES_CONTACTS = [
        self::TYPE_ALL_LABEL => self::TYPE_ALL,
        self::TYPE_LOCAL_LABEL => self::TYPE_LOCAL,
        self::TYPE_NATIONAL_LABEL => self::TYPE_NATIONAL,
        self::TYPE_NONE_LABEL => self::TYPE_NONE,
    ];

    public const NATIONAL_MANDATES = [
        self::DEPUTY,
        self::SENATOR,
        self::EURO_DEPUTY,
    ];

    public const LOCAL_MANDATES = [
        self::CITY_COUNCIL,
        self::EPCI_MEMBER,
        self::DEPARTMENTAL_COUNCIL,
        self::REGIONAL_COUNCIL,
        self::CORSICA_ASSEMBLY_MEMBER,
        self::BOROUGH_COUNCIL,
        self::CONSULAR_COUNCIL,
    ];

    public const ZONE_FILTER_BY_MANDATE = [
        self::BOROUGH_COUNCIL => [
            'types' => [Zone::BOROUGH],
        ],
        self::CITY_COUNCIL => [
            'types' => [Zone::BOROUGH, Zone::CITY],
        ],
        self::EPCI_MEMBER => [
            'types' => [Zone::CITY_COMMUNITY],
        ],
        self::DEPARTMENTAL_COUNCIL => [
            'types' => [Zone::CANTON],
        ],
        self::DEPUTY => [
            'types' => [Zone::DISTRICT],
        ],
        self::SENATOR => [
            'types' => [Zone::BOROUGH, Zone::DEPARTMENT, Zone::FOREIGN_DISTRICT, Zone::CUSTOM],
        ],
        self::REGIONAL_COUNCIL => [
            'types' => [Zone::DEPARTMENT],
        ],
        self::CORSICA_ASSEMBLY_MEMBER => [
            'types' => [Zone::REGION],
            'codes' => [self::CORSICA_REGION_CODE],
        ],
        self::EURO_DEPUTY => [
            'types' => [Zone::DEPARTMENT, Zone::COUNTRY],
        ],
        self::CONSULAR_COUNCIL => [
            'types' => [Zone::CONSULAR_DISTRICT],
        ],
    ];
}
