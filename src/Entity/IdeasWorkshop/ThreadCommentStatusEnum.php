<?php

namespace AppBundle\Entity\IdeasWorkshop;

use MyCLabs\Enum\Enum;

final class ThreadCommentStatusEnum extends Enum
{
    public const POSTED = 'POSTED';
    public const APPROVED = 'APPROVED';
    public const REPORTED = 'REPORTED';
    public const REFUSED = 'REFUSED';

    public const VISIBLE_STATUSES = [
        self::APPROVED,
        self::POSTED,
    ];
}
