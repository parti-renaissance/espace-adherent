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
    ];

    public const NATIONAL_SCOPES = [
        self::NATIONAL,
        self::NATIONAL_COMMUNICATION,
        self::PAP_NATIONAL_MANAGER,
        self::PHONING_NATIONAL_MANAGER,
    ];

    public const FOR_AUDIENCE_SEGMENT = [
        self::REFERENT,
        self::DEPUTY,
        self::SENATOR,
        self::CANDIDATE,
    ];
}
