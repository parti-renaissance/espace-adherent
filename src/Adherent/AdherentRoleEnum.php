<?php

namespace App\Adherent;

use MyCLabs\Enum\Enum;

class AdherentRoleEnum extends Enum
{
    public const REFERENT = 'referent';
    public const COREFERENT = 'coreferent';
    public const DELEGATED_REFERENT = 'delegated_referent';

    public const DEPUTY = 'deputy';
    public const DELEGATED_DEPUTY = 'delegated_deputy';

    public const SENATOR = 'senator';
    public const DELEGATED_SENATOR = 'delegated_senator';

    public const COMMITTEE_SUPERVISOR = 'committee_supervisor';
    public const COMMITTEE_PROVISIONAL_SUPERVISOR = 'committee_provisional_supervisor';
    public const COMMITTEE_HOST = 'committee_host';

    public const BOARD_MEMBER = 'board_member';

    public const COORDINATOR = 'coordinator';

    public const PROCURATION_MANAGER = 'procuration_manager';
    public const ASSESSOR_MANAGER = 'assessor_manager';
    public const ASSESSOR = 'assessor';
    public const JECOUTE_MANAGER = 'jecoute_manager';

    public const USER = 'user';

    public const LAREM = 'gq_ideas';

    public const ELECTED = 'elected';

    public const MUNICIPAL_CHIEF = 'municipal_chief';
    public const MUNICIPAL_MANAGER = 'municipal_manager';
    public const MUNICIPAL_MANAGER_SUPERVISOR = 'municipal_manager_supervisor';

    public const PRINT_PRIVILEGE = 'print_privilege';
    public const ROLE_NATIONAL = 'role_national';
    public const ELECTION_RESULTS_REPORTER = 'election_results_reporter';

    public const SENATORIAL_CANDIDATE = 'senatorial_candidate';
    public const LRE = 'lre';

    public const LEGISLATIVE_CANDIDATE = 'legislative_candidate';

    public const THEMATIC_COMMUNITY_CHIEF = 'thematic_community_chief';

    public const CANDIDATE_REGIONAL_HEADED = 'candidate_regional_headed';
    public const CANDIDATE_REGIONAL_LEADER = 'candidate_regional_leader';
    public const CANDIDATE_DEPARTMENTAL = 'candidate_departmental';

    public const DELEGATED_CANDIDATE_REGIONAL_HEADED = 'delegated_candidate_regional_headed';
    public const DELEGATED_CANDIDATE_REGIONAL_LEADER = 'delegated_candidate_regional_leader';
    public const DELEGATED_CANDIDATE_DEPARTMENTAL = 'delegated_candidate_departmental';

    public const COALITION_MODERATOR = 'coalition_moderator';
    public const CAUSE_AUTHOR = 'cause_author';

    public const ROLE_PHONING_MANAGER = 'role_phoning_manager';
    public const ROLE_PAP_NATIONAL_MANAGER = 'role_pap_national_manager';

    public static function getCandidates(): array
    {
        return [
            self::CANDIDATE_REGIONAL_HEADED,
            self::CANDIDATE_REGIONAL_LEADER,
            self::CANDIDATE_DEPARTMENTAL,
        ];
    }

    public static function getDelegatedCandidates(): array
    {
        return [
            self::DELEGATED_CANDIDATE_REGIONAL_HEADED,
            self::DELEGATED_CANDIDATE_REGIONAL_LEADER,
            self::DELEGATED_CANDIDATE_DEPARTMENTAL,
        ];
    }
}
