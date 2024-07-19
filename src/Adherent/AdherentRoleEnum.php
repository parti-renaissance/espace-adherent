<?php

namespace App\Adherent;

use MyCLabs\Enum\Enum;

class AdherentRoleEnum extends Enum
{
    public const DELEGATED_DEPUTY = 'delegated_deputy';

    public const SENATOR = 'senator';
    public const DELEGATED_SENATOR = 'delegated_senator';

    public const COMMITTEE_SUPERVISOR = 'committee_supervisor';
    public const ANIMATOR = 'animator';
    public const COMMITTEE_PROVISIONAL_SUPERVISOR = 'committee_provisional_supervisor';

    public const ONGOING_ELECTED_REPRESENTATIVE = 'ongoing_eletected_representative';

    public const PAP_USER = 'pap_user';
}
