<?php

namespace App\Scope;

use App\Entity\MyTeam\DelegatedAccess;
use MyCLabs\Enum\Enum;

class FeatureEnum extends Enum
{
    public const DASHBOARD = 'dashboard';
    public const CONTACTS = 'contacts';
    public const MESSAGES = 'messages';
    public const EVENTS = 'events';
    public const MY_TEAM = 'my_team';
    public const MOBILE_APP = 'mobile_app';
    public const ELECTIONS = 'elections';
    public const PHONING = 'phoning';
    public const PAP = 'pap';
    public const RIPOSTES = 'ripostes';
    public const TEAM = 'team';
    public const NEWS = 'news';
    public const PHONING_CAMPAIGN = 'phoning_campaign';
    public const SURVEY = 'survey';

    public const ALL = [
        self::DASHBOARD,
        self::CONTACTS,
        self::MESSAGES,
        self::EVENTS,
        self::MY_TEAM,
        self::MOBILE_APP,
        self::NEWS,
        self::ELECTIONS,
        self::RIPOSTES,
        self::PHONING,
        self::PAP,
        self::TEAM,
        self::PHONING_CAMPAIGN,
        self::SURVEY,
    ];

    public const DELEGATED_ACCESSES_MAPPING = [
        DelegatedAccess::ACCESS_ADHERENTS => self::CONTACTS,
        DelegatedAccess::ACCESS_MESSAGES => self::MESSAGES,
        DelegatedAccess::ACCESS_EVENTS => self::EVENTS,
        DelegatedAccess::ACCESS_JECOUTE => self::MOBILE_APP,
        DelegatedAccess::ACCESS_DASHBOARD => self::DASHBOARD,
        DelegatedAccess::ACCESS_NEWS => self::NEWS,
        DelegatedAccess::ACCESS_ELECTIONS => self::ELECTIONS,
        DelegatedAccess::ACCESS_RIPOSTES => self::RIPOSTES,
        DelegatedAccess::ACCESS_PHONING => self::PHONING,
        DelegatedAccess::ACCESS_PAP => self::PAP,
        DelegatedAccess::ACCESS_TEAM => self::TEAM,
        DelegatedAccess::ACCESS_PHONING_CAMPAIGN => self::PHONING_CAMPAIGN,
        DelegatedAccess::ACCESS_SURVEY => self::SURVEY,
    ];

    public const AVAILABLE_FOR_DELEGATED_ACCESSES = [
        self::DASHBOARD,
        self::CONTACTS,
        self::MESSAGES,
        self::EVENTS,
        self::NEWS,
        self::ELECTIONS,
        self::RIPOSTES,
        self::PHONING,
        self::PAP,
        self::TEAM,
        self::PHONING_CAMPAIGN,
        self::SURVEY,
    ];
}
