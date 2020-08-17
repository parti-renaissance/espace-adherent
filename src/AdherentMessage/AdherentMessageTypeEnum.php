<?php

namespace App\AdherentMessage;

use MyCLabs\Enum\Enum;

class AdherentMessageTypeEnum extends Enum
{
    public const DEPUTY = 'deputy';
    public const REFERENT = 'referent';
    public const COMMITTEE = 'committee';
    public const CITIZEN_PROJECT = 'citizen_project';
    public const MUNICIPAL_CHIEF = 'municipal_chief';
    public const SENATOR = 'senator';
    public const REFERENT_ELECTED_REPRESENTATIVE = 'referent_elected_representative';
    public const REFERENT_TERRITORIAL_COUNCIL = 'referent_territorial_council';
}
