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
    public const PHONING = 'phoning';

    public const ALL = [
        self::REFERENT,
        self::DEPUTY,
        self::SENATOR,
        self::CANDIDATE,
        self::NATIONAL,
        self::PHONING,
    ];

    public const FOR_AUDIENCE_SEGMENT = [
        self::REFERENT,
        self::DEPUTY,
        self::SENATOR,
        self::CANDIDATE,
    ];
}
