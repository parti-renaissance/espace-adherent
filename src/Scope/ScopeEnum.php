<?php

declare(strict_types=1);

namespace App\Scope;

use MyCLabs\Enum\Enum;

class ScopeEnum extends Enum
{
    public const DEPUTY = 'deputy';
    public const SENATOR = 'senator';
    public const CANDIDATE = 'candidate';
    public const MUNICIPAL_CANDIDATE = 'municipal_candidate';
    public const MUNICIPAL_PILOT = 'municipal_pilot';
    public const NATIONAL = 'national';
    public const NATIONAL_COMMUNICATION = 'national_communication';
    public const NATIONAL_TERRITORIES_DIVISION = 'national_territories_division';
    public const NATIONAL_ELECTED_REPRESENTATIVES_DIVISION = 'national_elected_representatives_division';
    public const NATIONAL_FORMATION_DIVISION = 'national_formation_division';
    public const NATIONAL_IDEAS_DIVISION = 'national_ideas_division';
    public const NATIONAL_TECH_DIVISION = 'national_tech_division';
    public const PHONING = 'phoning';
    public const PHONING_NATIONAL_MANAGER = 'phoning_national_manager';
    public const PAP_NATIONAL_MANAGER = 'pap_national_manager';
    public const CORRESPONDENT = 'correspondent';
    public const PAP = 'pap';
    public const MEETING_SCANNER = 'meeting_scanner';
    public const LEGISLATIVE_CANDIDATE = 'legislative_candidate';
    public const REGIONAL_COORDINATOR = 'regional_coordinator';
    public const REGIONAL_DELEGATE = 'regional_delegate';
    public const PRESIDENT_DEPARTMENTAL_ASSEMBLY = 'president_departmental_assembly';
    public const ANIMATOR = 'animator';
    public const PROCURATIONS_MANAGER = 'procurations_manager';
    public const FDE_COORDINATOR = 'fde_coordinator';
    public const AGORA_PRESIDENT = 'agora_president';
    public const AGORA_GENERAL_SECRETARY = 'agora_general_secretary';

    public const ALL = [
        self::DEPUTY,
        self::SENATOR,
        self::CANDIDATE,
        self::MUNICIPAL_CANDIDATE,
        self::MUNICIPAL_PILOT,

        self::NATIONAL,
        self::NATIONAL_COMMUNICATION,
        self::NATIONAL_TERRITORIES_DIVISION,
        self::NATIONAL_ELECTED_REPRESENTATIVES_DIVISION,
        self::NATIONAL_FORMATION_DIVISION,
        self::NATIONAL_IDEAS_DIVISION,
        self::NATIONAL_TECH_DIVISION,

        self::PHONING_NATIONAL_MANAGER,
        self::PAP_NATIONAL_MANAGER,
        self::CORRESPONDENT,
        self::LEGISLATIVE_CANDIDATE,
        self::REGIONAL_COORDINATOR,
        self::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
        self::ANIMATOR,
        self::REGIONAL_DELEGATE,
        self::PROCURATIONS_MANAGER,
        self::FDE_COORDINATOR,
        self::AGORA_PRESIDENT,
        self::AGORA_GENERAL_SECRETARY,

        self::PHONING,
        self::PAP,
        self::MEETING_SCANNER,
    ];

    public const NATIONAL_SCOPES = [
        self::NATIONAL,
        self::NATIONAL_COMMUNICATION,
        self::PAP_NATIONAL_MANAGER,
        self::PHONING_NATIONAL_MANAGER,
        self::NATIONAL_TERRITORIES_DIVISION,
        self::NATIONAL_ELECTED_REPRESENTATIVES_DIVISION,
        self::NATIONAL_FORMATION_DIVISION,
        self::NATIONAL_IDEAS_DIVISION,
        self::NATIONAL_TECH_DIVISION,
    ];

    public const SCOPE_INSTANCES = [
        self::PRESIDENT_DEPARTMENTAL_ASSEMBLY => 'Assemblée départementale',
        self::DEPUTY => 'Circonscription',
        self::MUNICIPAL_CANDIDATE => 'Municipales',
        self::MUNICIPAL_PILOT => 'Municipales',
        self::REGIONAL_DELEGATE => 'Région',
        self::ANIMATOR => 'Comité local',
        self::NATIONAL => 'National',
        self::NATIONAL_COMMUNICATION => 'National',
        self::NATIONAL_TERRITORIES_DIVISION => 'Pôle Territoires',
        self::NATIONAL_FORMATION_DIVISION => 'Pôle Formations',
        self::NATIONAL_IDEAS_DIVISION => 'Pôle Idées',
        self::NATIONAL_ELECTED_REPRESENTATIVES_DIVISION => 'Pôle Élus',
        self::NATIONAL_TECH_DIVISION => 'Pôle Tech',
        self::FDE_COORDINATOR => 'Français de l\'Étranger',
        self::REGIONAL_COORDINATOR => 'Région',
        self::LEGISLATIVE_CANDIDATE => 'Circonscription',
        self::AGORA_PRESIDENT => 'Agora',
        self::AGORA_GENERAL_SECRETARY => 'Agora',
    ];

    public static function isNational(?string $instanceScope): bool
    {
        return \in_array($instanceScope, self::NATIONAL_SCOPES, true);
    }
}
