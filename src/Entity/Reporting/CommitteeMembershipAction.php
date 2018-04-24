<?php

namespace AppBundle\Entity\Reporting;

use MyCLabs\Enum\Enum;

/**
 * @method static LEAVE()
 * @method static JOIN()
 */
class CommitteeMembershipAction extends Enum
{
    public const LEAVE = 'leave';
    public const JOIN = 'join';
}
