<?php

declare(strict_types=1);

namespace App\Adherent;

use App\Entity\Geo\Zone;

class MandateTypeEnum
{
    private const CORSICA_REGION_CODE = '94';
    private const FRANCE_COUNTRY_CODE = 'FR';

    public const DEPUTE_EUROPEEN = 'depute_europeen';
    public const SENATEUR = 'senateur';
    public const DEPUTE = 'depute';
    public const PRESIDENT_CONSEIL_REGIONAL = 'president_conseil_regional';
    public const CONSEILLER_REGIONAL = 'conseiller_regional';
    public const PRESIDENT_CONSEIL_DEPARTEMENTAL = 'president_conseil_departemental';
    public const CONSEILLER_DEPARTEMENTAL = 'conseiller_departemental';
    public const CONSEILLER_TERRITORIAL = 'conseiller_territorial';
    public const PRESIDENT_CONSEIL_COMMUNAUTAIRE = 'president_conseil_communautaire';
    public const CONSEILLER_COMMUNAUTAIRE = 'conseiller_communautaire';
    public const MAIRE = 'maire';
    public const CONSEILLER_MUNICIPAL = 'conseiller_municipal';
    public const CONSEILLER_ARRONDISSEMENT = 'conseiller_arrondissement';
    public const MEMBRE_ASSEMBLEE_FDE = 'membre_assemblee_fde';
    public const CONSEILLER_FDE = 'conseiller_fde';
    public const DELEGUE_CONSULAIRE = 'delegue_consulaire';
    public const MINISTER = 'ministre';

    public const ALL = [
        self::DEPUTE_EUROPEEN,
        self::SENATEUR,
        self::DEPUTE,
        self::PRESIDENT_CONSEIL_REGIONAL,
        self::CONSEILLER_REGIONAL,
        self::PRESIDENT_CONSEIL_DEPARTEMENTAL,
        self::CONSEILLER_DEPARTEMENTAL,
        self::CONSEILLER_TERRITORIAL,
        self::PRESIDENT_CONSEIL_COMMUNAUTAIRE,
        self::CONSEILLER_COMMUNAUTAIRE,
        self::MAIRE,
        self::CONSEILLER_MUNICIPAL,
        self::CONSEILLER_ARRONDISSEMENT,
        self::MEMBRE_ASSEMBLEE_FDE,
        self::CONSEILLER_FDE,
        self::DELEGUE_CONSULAIRE,
        self::MINISTER,
    ];

    public const ZONE_FILTER_BY_MANDATE = [
        self::DEPUTE_EUROPEEN => [
            'types' => [
                Zone::DEPARTMENT,
                Zone::COUNTRY,
            ],
        ],
        self::SENATEUR => [
            'types' => [
                Zone::BOROUGH,
                Zone::DEPARTMENT,
                Zone::FOREIGN_DISTRICT,
                Zone::CUSTOM,
            ],
        ],
        self::DEPUTE => [
            'types' => [Zone::DISTRICT, Zone::FOREIGN_DISTRICT],
        ],
        self::PRESIDENT_CONSEIL_REGIONAL => [
            'types' => [Zone::DEPARTMENT],
        ],
        self::CONSEILLER_REGIONAL => [
            'types' => [Zone::DEPARTMENT],
        ],
        self::PRESIDENT_CONSEIL_DEPARTEMENTAL => [
            'types' => [Zone::CANTON],
        ],
        self::CONSEILLER_DEPARTEMENTAL => [
            'types' => [Zone::CANTON],
        ],
        self::CONSEILLER_TERRITORIAL => [
            'types' => [Zone::REGION],
            'codes' => [self::CORSICA_REGION_CODE],
        ],
        self::PRESIDENT_CONSEIL_COMMUNAUTAIRE => [
            'types' => [Zone::CITY_COMMUNITY],
        ],
        self::CONSEILLER_COMMUNAUTAIRE => [
            'types' => [Zone::CITY_COMMUNITY],
        ],
        self::MAIRE => [
            'types' => [
                Zone::BOROUGH,
                Zone::CITY,
            ],
        ],
        self::CONSEILLER_MUNICIPAL => [
            'types' => [
                Zone::BOROUGH,
                Zone::CITY,
            ],
        ],
        self::CONSEILLER_ARRONDISSEMENT => [
            'types' => [Zone::BOROUGH],
        ],
        self::MEMBRE_ASSEMBLEE_FDE => [
            'types' => [Zone::CONSULAR_DISTRICT],
        ],
        self::CONSEILLER_FDE => [
            'types' => [Zone::CONSULAR_DISTRICT],
        ],
        self::DELEGUE_CONSULAIRE => [
            'types' => [Zone::CONSULAR_DISTRICT],
        ],
        self::MINISTER => [
            'types' => [Zone::COUNTRY],
            'codes' => [self::FRANCE_COUNTRY_CODE],
        ],
    ];

    public const LOCAL_TYPES = [
        self::PRESIDENT_CONSEIL_REGIONAL,
        self::CONSEILLER_REGIONAL,
        self::PRESIDENT_CONSEIL_DEPARTEMENTAL,
        self::CONSEILLER_DEPARTEMENTAL,
        self::CONSEILLER_TERRITORIAL,
        self::PRESIDENT_CONSEIL_COMMUNAUTAIRE,
        self::CONSEILLER_COMMUNAUTAIRE,
        self::MAIRE,
        self::CONSEILLER_MUNICIPAL,
        self::CONSEILLER_ARRONDISSEMENT,
        self::MEMBRE_ASSEMBLEE_FDE,
        self::CONSEILLER_FDE,
        self::DELEGUE_CONSULAIRE,
    ];

    public const PARLIAMENTARY_TYPES = [
        self::SENATEUR,
        self::DEPUTE,
        self::DEPUTE_EUROPEEN,
    ];
}
