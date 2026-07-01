<?php

declare(strict_types=1);

namespace App\Scope;

use MyCLabs\Enum\Enum;

class FeatureEnum extends Enum
{
    public const DASHBOARD = 'dashboard';
    public const CONTACTS = 'contacts';
    public const CONTACTS_EXPORT = 'contacts_export';
    public const CHATBOT = 'chatbot';
    public const AI_ANTISECHE = 'ai_antiseche';
    public const MESSAGES = 'messages';
    public const PUBLICATIONS = 'publications';
    public const PUBLICATIONS_CADRES = 'publications_cadres';
    public const EVENTS = 'events';
    public const MY_TEAM = 'my_team';
    public const MY_TEAM_CUSTOM_ROLE = 'my_team_custom_role';
    public const MOBILE_APP = 'mobile_app';
    public const ELECTIONS = 'elections';
    public const PAP = 'pap';
    public const PAP_USER = 'pap_user';
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
    public const NATIONAL_EVENT = 'national_event';
    public const EAGGLE = 'eaggle';

    public const ALL = [
        self::DASHBOARD,
        self::CONTACTS,
        self::CONTACTS_EXPORT,
        self::CHATBOT,
        self::AI_ANTISECHE,
        self::MESSAGES,
        self::PUBLICATIONS,
        self::PUBLICATIONS_CADRES,
        self::EVENTS,
        self::MY_TEAM,
        self::MY_TEAM_CUSTOM_ROLE,
        self::MOBILE_APP,
        self::NEWS,
        self::ELECTIONS,
        self::RIPOSTES,
        self::PAP,
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
        self::EAGGLE,
        self::CIRCONSCRIPTIONS,
        self::REFERRALS,
        self::NATIONAL_EVENT,
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

    /**
     * Features that may be granted to the militant scope (its global config is restricted to this whitelist).
     */
    public static function getMilitantFeatures(): array
    {
        return [
            self::EVENTS,
            self::ACTIONS,
            self::AI_ANTISECHE,
            self::PAP_USER,
        ];
    }

    public static function getAssignableFeatures(): array
    {
        return array_values(array_unique(array_merge(self::ALL, self::getMilitantFeatures())));
    }

    public static function getChatbotFeatures(): array
    {
        return [self::AI_ANTISECHE];
    }

    public static function getAgentIdForFeature(string $feature): ?string
    {
        return match ($feature) {
            self::AI_ANTISECHE => 'antiseche',
            default => null,
        };
    }

    public static function getFeatureForAgentId(string $agentId): ?string
    {
        return match ($agentId) {
            'antiseche' => self::AI_ANTISECHE,
            default => null,
        };
    }
}
