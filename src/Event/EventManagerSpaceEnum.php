<?php

namespace App\Event;

use MyCLabs\Enum\Enum;

class EventManagerSpaceEnum extends Enum
{
    public const REFERENT = 'referent';
    public const DEPUTY = 'deputy';
    public const SENATOR = 'senator';
    public const CANDIDATE = 'candidate';
}
