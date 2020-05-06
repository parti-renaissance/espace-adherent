<?php

namespace App\Event;

use MyCLabs\Enum\Enum;

class EventManagerSpaceEnum extends Enum
{
    public const REFERENT = 'referent';
    public const MUNICIPAL_CHIEF = 'municipal_chief';
    public const DEPUTY = 'deputy';
    public const SENATOR = 'senator';
}
