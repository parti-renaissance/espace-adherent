<?php

namespace App\Scope;

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

    public const DELEGATED_ACCESSES_BY_DEFAULT = [
        self::DASHBOARD,
        self::MOBILE_APP,
    ];

    public const AVAILABLE_FOR_DELEGATED_ACCESSES = [
        self::DASHBOARD,
        self::MOBILE_APP,
        self::CONTACTS,
        self::MESSAGES,
        self::EVENTS,
        self::NEWS,
        self::ELECTIONS,
        self::PAP,
        self::TEAM,
        self::PHONING_CAMPAIGN,
        self::SURVEY,
    ];
}
