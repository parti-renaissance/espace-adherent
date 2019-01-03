<?php

namespace AppBundle\Entity\IdeasWorkshop;

final class ThreadCommentStatusEnum
{
    public const POSTED = 'POSTED';
    public const APPROVED = 'APPROVED';
    public const REPORTED = 'REPORTED';
    public const REFUSED = 'REFUSED';

    public const VISIBLE_STATUSES = [
        self::APPROVED,
        self::POSTED,
    ];

    public const ALL_STATUSES = [
        self::POSTED,
        self::APPROVED,
        self::REPORTED,
        self::REFUSED,
    ];
}
