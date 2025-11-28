<?php

declare(strict_types=1);

namespace App\Entity\TerritorialCouncil;

use MyCLabs\Enum\Enum;

class TerritorialCouncilQualityEnum extends Enum
{
    public const REFERENT = 'referent';
    public const GOVERNMENT_MEMBER = 'government_member';
    public const REFERENT_JAM = 'referent_jam';
    public const SENATOR = 'senator';
    public const DEPUTY = 'deputy';
    public const EUROPEAN_DEPUTY = 'european_deputy';
    public const REGIONAL_COUNCIL_PRESIDENT = 'regional_council_president';
    public const DEPARTMENTAL_COUNCIL_PRESIDENT = 'departmental_council_president';
    public const MAYOR = 'mayor';
    public const LEADER = 'leader';
    public const REGIONAL_COUNCILOR = 'regional_councilor';
    public const CORSICA_ASSEMBLY_MEMBER = 'corsica_assembly_member';
    public const DEPARTMENT_COUNCILOR = 'department_councilor';
    public const CITY_COUNCILOR = 'city_councilor';
    public const BOROUGH_COUNCILOR = 'borough_councilor';
    public const CONSULAR_COUNCILOR = 'consular_councilor';
    public const COMMITTEE_SUPERVISOR = 'committee_supervisor';
    public const ELECTED_CANDIDATE_ADHERENT = 'elected_candidate_adherent';

    public const ALL = [
        self::REFERENT,
        self::GOVERNMENT_MEMBER,
        self::REFERENT_JAM,
        self::SENATOR,
        self::DEPUTY,
        self::EUROPEAN_DEPUTY,
        self::REGIONAL_COUNCIL_PRESIDENT,
        self::DEPARTMENTAL_COUNCIL_PRESIDENT,
        self::MAYOR,
        self::REGIONAL_COUNCILOR,
        self::CORSICA_ASSEMBLY_MEMBER,
        self::DEPARTMENT_COUNCILOR,
        self::CITY_COUNCILOR,
        self::BOROUGH_COUNCILOR,
        self::CONSULAR_COUNCILOR,
        self::COMMITTEE_SUPERVISOR,
        self::ELECTED_CANDIDATE_ADHERENT,
    ];

    public const POLITICAL_COMMITTEE_ELECTED_MEMBERS = [
        self::REGIONAL_COUNCILOR,
        self::CORSICA_ASSEMBLY_MEMBER,
        self::DEPARTMENT_COUNCILOR,
        self::CITY_COUNCILOR,
        self::BOROUGH_COUNCILOR,
        self::CONSULAR_COUNCILOR,
        self::COMMITTEE_SUPERVISOR,
        self::ELECTED_CANDIDATE_ADHERENT,
    ];
}
