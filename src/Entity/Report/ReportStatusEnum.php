<?php

namespace AppBundle\Entity\Report;

use MyCLabs\Enum\Enum;

class ReportStatusEnum extends Enum
{
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_UNRESOLVED = 'unresolved';
}
