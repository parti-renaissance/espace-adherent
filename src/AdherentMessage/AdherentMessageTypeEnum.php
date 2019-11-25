<?php

namespace AppBundle\AdherentMessage;

use MyCLabs\Enum\Enum;

class AdherentMessageTypeEnum extends Enum
{
    public const DEPUTY = 'deputy';
    public const REFERENT = 'referent';
    public const COMMITTEE = 'committee';
    public const CITIZEN_PROJECT = 'citizen_project';
    public const MUNICIPAL_CHIEF = 'municipal_chief';
    public const SENATOR = 'senator';
}
