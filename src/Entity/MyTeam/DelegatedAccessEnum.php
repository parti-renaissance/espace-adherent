<?php

namespace App\Entity\MyTeam;

use MyCLabs\Enum\Enum;

class DelegatedAccessEnum extends Enum
{
    public const TYPE_REFERENT = 'referent';
    public const TYPE_DEPUTY = 'deputy';
    public const TYPE_CANDIDATE = 'candidate';

    public const TYPES = [
        self::TYPE_REFERENT,
        self::TYPE_DEPUTY,
        self::TYPE_CANDIDATE,
    ];

    public static function getAccessesForType(string $type): array
    {
        $accesses = DelegatedAccess::ACCESSES;
        if (self::TYPE_DEPUTY === $type) {
            $accesses[] = DelegatedAccess::ACCESS_COMMITTEE;
        }

        if (self::TYPE_REFERENT === $type) {
            $accesses[] = DelegatedAccess::ACCESS_JECOUTE;
            $accesses[] = DelegatedAccess::ACCESS_ELECTED_REPRESENTATIVES;
            $accesses[] = DelegatedAccess::ACCESS_COMMITTEE;
        }

        if (self::TYPE_CANDIDATE === $type) {
            $accesses[] = DelegatedAccess::ACCESS_JECOUTE;
            $accesses[] = DelegatedAccess::ACCESS_JECOUTE_REGION;
            $accesses[] = DelegatedAccess::ACCESS_JECOUTE_NEWS;
            $accesses[] = DelegatedAccess::ACCESS_POLLS;
            $accesses[] = DelegatedAccess::ACCESS_FILES;
        }

        return $accesses;
    }

    public static function getDelegatedAccessRoutes(string $type): array
    {
        return [
            DelegatedAccess::ACCESS_ADHERENTS => "app_{$type}_managed_users_list",
            DelegatedAccess::ACCESS_EVENTS => "app_{$type}_event_manager_events",
            DelegatedAccess::ACCESS_COMMITTEE => "app_{$type}_committees",
            DelegatedAccess::ACCESS_POLLS => "app_{$type}_polls_local_list",
            DelegatedAccess::ACCESS_JECOUTE => "app_jecoute_{$type}_local_surveys_list",
            DelegatedAccess::ACCESS_JECOUTE_REGION => 'app_jecoute_candidate_region_edit',
            DelegatedAccess::ACCESS_JECOUTE_NEWS => 'app_jecoute_news_candidate_news_list',
            DelegatedAccess::ACCESS_ELECTED_REPRESENTATIVES => "app_{$type}_elected_representatives_list",
            DelegatedAccess::ACCESS_FILES => "app_{$type}_files_list",
        ];
    }

    public static function getStandardRoute(string $type): string
    {
        return [
            'committee' => 'app_committee_space_dashboard',
            'coordinator_committees' => 'app_coordinator_committees',
            'senatorial_candidate_elected_representatives' => 'app_senatorial_candidate_elected_representatives_list',
            'jecoute_manager_local_surveys' => 'app_jecoute_manager_local_surveys_list',
            'election_results_reporter_space_cities' => 'app_election_results_reporter_space_cities_list',
        ][$type] ?? "app_{$type}_managed_users_list";
    }
}
