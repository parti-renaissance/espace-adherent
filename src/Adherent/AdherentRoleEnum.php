<?php

namespace AppBundle\Adherent;

use MyCLabs\Enum\Enum;

class AdherentRoleEnum extends Enum
{
    public const REFERENT = 'referent';
    public const COREFERENT = 'coreferent';

    public const DEPUTY = 'deputy';

    public const COMMITTEE_SUPERVISOR = 'committee_supervisor';
    public const COMMITTEE_HOST = 'committee_host';

    public const CITIZEN_PROJECT_HOLDER = 'citizen_project_holder';

    public const BOARD_MEMBER = 'board_member';

    public const COORDINATOR = 'coordinator';
    public const REC = 'rec';

    public const PROCURATION_MANAGER = 'procuration_manager';
    public const ASSESSOR_MANAGER = 'assessor_manager';

    public const USER = 'user';

    public const LAREM = 'gq_ideas';

    public const ELECTED = 'elected';

    public const MUNICIPAL_CHIEF = 'municipal_chief';
}
