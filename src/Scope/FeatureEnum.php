<?php

namespace App\Scope;

use MyCLabs\Enum\Enum;

class FeatureEnum extends Enum
{
    public const DASHBOARD = 'dashboard';
    public const CONTACTS = 'contacts';
    public const CONTACTS_EXPORT = 'contacts_export';
    public const MESSAGES = 'messages';
    public const PUBLICATIONS = 'publications';
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
    public const DEPARTMENT_SITE = 'department_site';
    public const ELECTED_REPRESENTATIVE = 'elected_representative';
    public const ADHERENT_FORMATIONS = 'adherent_formations';
    public const COMMITTEE = 'committee';
    public const GENERAL_MEETING_REPORTS = 'general_meeting_reports';
    public const DOCUMENTS = 'documents';
    public const DESIGNATION = 'designation';
    public const STATUTORY_MESSAGE = 'statutory_message';
    public const PROCURATIONS = 'procurations';
    public const ACTIONS = 'actions';
    public const FEATUREBASE = 'featurebase';
    public const CIRCONSCRIPTIONS = 'circonscriptions';
    public const REFERRALS = 'referrals';
    public const AGORAS = 'agoras';

    public const ALL = [
        self::DASHBOARD,
        self::CONTACTS,
        self::CONTACTS_EXPORT,
        self::MESSAGES,
        self::PUBLICATIONS,
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
        self::DEPARTMENT_SITE,
        self::ELECTED_REPRESENTATIVE,
        self::ADHERENT_FORMATIONS,
        self::COMMITTEE,
        self::GENERAL_MEETING_REPORTS,
        self::DOCUMENTS,
        self::DESIGNATION,
        self::STATUTORY_MESSAGE,
        self::PROCURATIONS,
        self::ACTIONS,
        self::FEATUREBASE,
        self::CIRCONSCRIPTIONS,
        self::REFERRALS,
    ];

    public const DELEGATED_ACCESSES_BY_DEFAULT = [
        self::DASHBOARD,
        self::MOBILE_APP,
    ];

    public static function getDelegatableFeatures(): array
    {
        return array_diff(self::ALL, [
            self::FEATUREBASE,
            self::CONTACTS_EXPORT,
        ]);
    }
}
