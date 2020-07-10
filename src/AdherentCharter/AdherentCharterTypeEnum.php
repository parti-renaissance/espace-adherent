<?php

namespace App\AdherentCharter;

use MyCLabs\Enum\Enum;

class AdherentCharterTypeEnum extends Enum
{
    public const TYPE_REFERENT = 'referent';
    public const TYPE_DEPUTY = 'deputy';
    public const TYPE_SENATOR = 'senator';
    public const TYPE_MUNICIPAL_CHIEF = 'municipal_chief';
    public const TYPE_SENATORIAL_CANDIDATE = 'senatorial_candidate';
    public const TYPE_LRE = 'la_republique_ensemble';
}
