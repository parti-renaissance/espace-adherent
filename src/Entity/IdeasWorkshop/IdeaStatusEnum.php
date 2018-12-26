<?php

namespace AppBundle\Entity\IdeasWorkshop;

use MyCLabs\Enum\Enum;

final class IdeaStatusEnum extends Enum
{
    public const DRAFT = 'DRAFT';
    public const PENDING = 'PENDING';
    public const FINALIZED = 'FINALIZED';
    public const UNPUBLISHED = 'UNPUBLISHED';

    public const VISIBLE_STATUSES = [
        self::PENDING,
        self::FINALIZED,
    ];

    public const ALL_STATUSES = [
        self::DRAFT,
        self::PENDING,
        self::FINALIZED,
        self::UNPUBLISHED,
    ];
}
