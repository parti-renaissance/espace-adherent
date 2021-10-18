<?php

namespace App\Scope;

use App\Entity\MyTeam\DelegatedAccess;
use MyCLabs\Enum\Enum;

class FeatureEnum extends Enum
{
    public const DASHBOARD = 'dashboard';
    public const CONTACTS = 'contacts';
    public const MESSAGES = 'messages';
    public const MOBILE_APP = 'mobile_app';
    public const ELECTIONS = 'elections';
    public const PHONING = 'phoning';
    public const PAP = 'pap';
    public const RIPOSTES = 'ripostes';
    public const TEAM = 'team';

    public const ALL = [
        self::DASHBOARD,
        self::CONTACTS,
        self::MESSAGES,
        self::MOBILE_APP,
        self::ELECTIONS,
        self::RIPOSTES,
        self::PHONING,
        self::PAP,
        self::TEAM,
    ];

    public const DELEGATED_ACCESSES_MAPPING = [
        DelegatedAccess::ACCESS_ADHERENTS => self::CONTACTS,
        DelegatedAccess::ACCESS_MESSAGES => self::MESSAGES,
        DelegatedAccess::ACCESS_JECOUTE => self::MOBILE_APP,
    ];
}
