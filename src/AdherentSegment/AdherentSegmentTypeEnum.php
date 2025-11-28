<?php

declare(strict_types=1);

namespace App\AdherentSegment;

use MyCLabs\Enum\Enum;

class AdherentSegmentTypeEnum extends Enum
{
    public const TYPE_REFERENT = 'referent';
    public const TYPE_COMMITTEE = 'committee';
}
