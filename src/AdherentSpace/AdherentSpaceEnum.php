<?php

namespace App\AdherentSpace;

use App\Scope\ScopeEnum;

class AdherentSpaceEnum
{
    public const DEPUTY = 'deputy';
    public const LRE = 'lre';
    public const REFERENT = 'referent';
    public const SENATOR = 'senator';
    public const SENATORIAL_CANDIDATE = 'senatorial_candidate';
    public const CANDIDATE = 'candidate';
    public const CANDIDATE_JECOUTE = 'candidate_jecoute';
    public const LEGISLATIVE_CANDIDATE = 'legislative_candidate';
    public const MUNICIPAL_CHIEF = 'municipal_chief';
    public const MUNICIPAL_MANAGER = 'municipal_manager';
    public const MUNICIPAL_MANAGER_SUPERVISOR = 'municipal_manager_supervisor';
    public const ASSESSOR = 'assessor';
    public const CORRESPONDENT = 'correspondent';

    public const SCOPES = [
        ScopeEnum::REFERENT => self::REFERENT,
        ScopeEnum::CANDIDATE => self::CANDIDATE,
        ScopeEnum::SENATOR => self::SENATOR,
        ScopeEnum::DEPUTY => self::DEPUTY,
        ScopeEnum::CORRESPONDENT => self::CORRESPONDENT,
    ];
}
