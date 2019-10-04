<?php

namespace AppBundle\AdherentCharter;

use MyCLabs\Enum\Enum;

class AdherentCharterTypeEnum extends Enum
{
    public const TYPE_REFERENT = 'referent';
    public const TYPE_DEPUTY = 'deputy';
    public const TYPE_MUNICIPAL_CHIEF = 'municipal_chief';
}
