<?php

namespace App\AdherentSpace;

use App\Scope\ScopeEnum;

class AdherentSpaceEnum
{
    public const DEPUTY = 'deputy';
    public const REFERENT = 'referent';
    public const SENATOR = 'senator';
    public const SENATORIAL_CANDIDATE = 'senatorial_candidate';
    public const CANDIDATE = 'candidate';
    public const CANDIDATE_JECOUTE = 'candidate_jecoute';
    public const LEGISLATIVE_CANDIDATE = 'legislative_candidate';
    public const CORRESPONDENT = 'correspondent';
    public const REGIONAL_COORDINATOR = 'regional_coordinator';

    public const SCOPES = [
        ScopeEnum::CANDIDATE => self::CANDIDATE,
        ScopeEnum::SENATOR => self::SENATOR,
        ScopeEnum::DEPUTY => self::DEPUTY,
        ScopeEnum::CORRESPONDENT => self::CORRESPONDENT,
        ScopeEnum::REGIONAL_COORDINATOR => self::REGIONAL_COORDINATOR,
    ];
}
