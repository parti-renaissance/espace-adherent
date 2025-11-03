<?php

namespace App\Scope;

use MyCLabs\Enum\Enum;

class ScopeEnum extends Enum
{
    public const DEPUTY = 'deputy';
    public const SENATOR = 'senator';
    public const CANDIDATE = 'candidate';
    public const MUNICIPAL_CANDIDATE = 'municipal_candidate';
    public const NATIONAL = 'national';
    public const NATIONAL_COMMUNICATION = 'national_communication';
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
        self::NATIONAL,
        self::NATIONAL_COMMUNICATION,
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
    ];

    public const ROLE_NAMES = [
        self::PRESIDENT_DEPARTMENTAL_ASSEMBLY => 'Président',
        self::MUNICIPAL_CANDIDATE => 'Candidat aux municipales',
        self::DEPUTY => 'Délégué',
        self::REGIONAL_DELEGATE => 'Délégué',
        self::ANIMATOR => 'Responsable',
        self::NATIONAL => 'Responsable',
        self::FDE_COORDINATOR => 'Coordinateur',
        self::REGIONAL_COORDINATOR => 'Coordinateur',
        self::LEGISLATIVE_CANDIDATE => 'Candidat',
        self::AGORA_PRESIDENT => 'Président',
        self::AGORA_GENERAL_SECRETARY => 'Secrétaire Général',
    ];

    public const SCOPE_INSTANCES = [
        self::PRESIDENT_DEPARTMENTAL_ASSEMBLY => 'Assemblée départementale',
        self::DEPUTY => 'Circonscription',
        self::REGIONAL_DELEGATE => 'Région',
        self::ANIMATOR => 'Comité local',
        self::NATIONAL => 'National',
        self::FDE_COORDINATOR => 'Français de l\'Étranger',
        self::REGIONAL_COORDINATOR => 'Région',
        self::LEGISLATIVE_CANDIDATE => 'Circonscription',
        self::AGORA_PRESIDENT => 'Agora',
        self::AGORA_GENERAL_SECRETARY => 'Agora',
    ];
}
