<?php

namespace AppBundle\AdherentSegment;

use MyCLabs\Enum\Enum;

class AdherentSegmentTypeEnum extends Enum
{
    public const TYPE_REFERENT = 'referent';
    public const TYPE_COMMITTEE = 'committee';
    public const TYPE_CITIZEN_PROJECT = 'citizen_project';
}
