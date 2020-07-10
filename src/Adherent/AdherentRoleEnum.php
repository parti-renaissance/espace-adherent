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
    public const COMMITTEE_HOST = 'committee_host';

    public const CITIZEN_PROJECT_HOLDER = 'citizen_project_holder';

    public const BOARD_MEMBER = 'board_member';

    public const COORDINATOR = 'coordinator';
    public const REC = 'rec';

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
    public const ELECTION_RESULTS_REPORTER = 'election_results_reporter';

    public const SENATORIAL_CANDIDATE = 'senatorial_candidate';
    public const LRE = 'lre';
}
