<?php

namespace App\Adherent;

use MyCLabs\Enum\Enum;

class AdherentRoleEnum extends Enum
{
    public const REFERENT = 'referent';
    public const COREFERENT = 'coreferent';
    public const DELEGATED_REFERENT = 'delegated_referent';

    public const DELEGATED_DEPUTY = 'delegated_deputy';

    public const SENATOR = 'senator';
    public const DELEGATED_SENATOR = 'delegated_senator';

    public const COMMITTEE_SUPERVISOR = 'committee_supervisor';
    public const ANIMATOR = 'animator';
    public const COMMITTEE_PROVISIONAL_SUPERVISOR = 'committee_provisional_supervisor';
    public const COMMITTEE_HOST = 'committee_host';

    public const BOARD_MEMBER = 'board_member';

    public const PROCURATION_MANAGER = 'procuration_manager';
    public const ASSESSOR_MANAGER = 'assessor_manager';
    public const ASSESSOR = 'assessor';
    public const JECOUTE_MANAGER = 'jecoute_manager';

    public const USER = 'user';

    public const ELECTED = 'elected';
    public const ONGOING_ELECTED_REPRESENTATIVE = 'ongoing_eletected_representative';

    public const ROLE_NATIONAL = 'role_national';
    public const ROLE_NATIONAL_COMMUNICATION = 'role_national_communication';
    public const ELECTION_RESULTS_REPORTER = 'election_results_reporter';

    public const SENATORIAL_CANDIDATE = 'senatorial_candidate';
    public const THEMATIC_COMMUNITY_CHIEF = 'thematic_community_chief';

    public const CANDIDATE_REGIONAL_HEADED = 'candidate_regional_headed';
    public const CANDIDATE_REGIONAL_LEADER = 'candidate_regional_leader';
    public const CANDIDATE_DEPARTMENTAL = 'candidate_departmental';

    public const DELEGATED_CANDIDATE_REGIONAL_HEADED = 'delegated_candidate_regional_headed';
    public const DELEGATED_CANDIDATE_REGIONAL_LEADER = 'delegated_candidate_regional_leader';
    public const DELEGATED_CANDIDATE_DEPARTMENTAL = 'delegated_candidate_departmental';

    public const ROLE_PHONING_MANAGER = 'role_phoning_manager';
    public const ROLE_PAP_NATIONAL_MANAGER = 'role_pap_national_manager';
    public const ROLE_PAP_USER = 'role_pap_user';

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
