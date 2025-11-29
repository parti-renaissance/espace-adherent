<?php

declare(strict_types=1);

namespace App\Adherent;

use MyCLabs\Enum\Enum;

class AdherentRoleEnum extends Enum
{
    public const COMMITTEE_SUPERVISOR = 'committee_supervisor';
    public const ONGOING_ELECTED_REPRESENTATIVE = 'ongoing_eletected_representative';
    public const PAP_USER = 'pap_user';

    public const DELEGATED_PRESIDENT_DEPARTMENTAL_ASSEMBLY = 'delegated_president_departmental_assembly';
    public const DELEGATED_DEPUTY = 'delegated_deputy';
    public const DELEGATED_ANIMATOR = 'delegated_animator';
}
