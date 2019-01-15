<?php

namespace AppBundle\Entity\IdeasWorkshop;

final class IdeaStatusEnum
{
    public const DRAFT = 'DRAFT';
    public const PENDING = 'PENDING';
    public const FINALIZED = 'FINALIZED';
    public const UNPUBLISHED = 'UNPUBLISHED';

    public const ALL_STATUSES = [
        self::DRAFT,
        self::PENDING,
        self::FINALIZED,
        self::UNPUBLISHED,
    ];
}
