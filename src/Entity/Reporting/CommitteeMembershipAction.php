<?php

declare(strict_types=1);

namespace App\Entity\Reporting;

use MyCLabs\Enum\Enum;

/**
 * @method static CommitteeMembershipAction LEAVE()
 * @method static CommitteeMembershipAction JOIN()
 */
class CommitteeMembershipAction extends Enum
{
    public const LEAVE = 'leave';
    public const JOIN = 'join';
}
