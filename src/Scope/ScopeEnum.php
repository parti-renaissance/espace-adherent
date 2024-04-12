<?php

namespace App\Scope;

use MyCLabs\Enum\Enum;

class ScopeEnum extends Enum
{
    public const REFERENT = 'referent';
    public const DEPUTY = 'deputy';
    public const SENATOR = 'senator';
    public const CANDIDATE = 'candidate';
    public const NATIONAL = 'national';
    public const NATIONAL_COMMUNICATION = 'national_communication';
    public const PHONING = 'phoning';
    public const PHONING_NATIONAL_MANAGER = 'phoning_national_manager';
    public const PAP_NATIONAL_MANAGER = 'pap_national_manager';
    public const CORRESPONDENT = 'correspondent';
    public const PAP = 'pap';
    public const LEGISLATIVE_CANDIDATE = 'legislative_candidate';
    public const REGIONAL_COORDINATOR = 'regional_coordinator';
    public const REGIONAL_DELEGATE = 'regional_delegate';
    public const PRESIDENT_DEPARTMENTAL_ASSEMBLY = 'president_departmental_assembly';
    public const ANIMATOR = 'animator';
    public const PROCURATIONS_MANAGER = 'procurations_manager';
    public const FDE_COORDINATOR = 'fde_coordinator';

    public const ALL = [
        self::REFERENT,
        self::DEPUTY,
        self::SENATOR,
        self::CANDIDATE,
        self::NATIONAL,
        self::NATIONAL_COMMUNICATION,
        self::PHONING,
        self::PHONING_NATIONAL_MANAGER,
        self::PAP_NATIONAL_MANAGER,
        self::PAP,
        self::CORRESPONDENT,
        self::LEGISLATIVE_CANDIDATE,
        self::REGIONAL_COORDINATOR,
        self::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
        self::ANIMATOR,
        self::REGIONAL_DELEGATE,
        self::PROCURATIONS_MANAGER,
        self::FDE_COORDINATOR,
    ];

    public const NATIONAL_SCOPES = [
        self::NATIONAL,
        self::NATIONAL_COMMUNICATION,
        self::PAP_NATIONAL_MANAGER,
        self::PHONING_NATIONAL_MANAGER,
    ];
}
