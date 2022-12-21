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
    public const PAP = 'pap';
    public const PAP_V2 = 'pap_v2';
    public const RIPOSTES = 'ripostes';
    public const TEAM = 'team';
    public const NEWS = 'news';
    public const PHONING_CAMPAIGN = 'phoning_campaign';
    public const SURVEY = 'survey';

    public const department_site = 'department_site';

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
        self::PAP,
        self::PAP_V2,
        self::TEAM,
        self::PHONING_CAMPAIGN,
        self::SURVEY,
        self::department_site,
    ];

    public const DELEGATED_ACCESSES_BY_DEFAULT = [
        self::DASHBOARD,
        self::MOBILE_APP,
    ];

    public const FORBIDDEN_FOR_DELEGATED_ACCESSES = [
        self::MY_TEAM,
    ];

    public static function getAvailableForDelegatedAccess(): array
    {
        return array_diff(self::ALL, self::FORBIDDEN_FOR_DELEGATED_ACCESSES);
    }
}
