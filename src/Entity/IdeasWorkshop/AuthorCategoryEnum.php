<?php

namespace AppBundle\Entity\IdeasWorkshop;

use MyCLabs\Enum\Enum;

final class AuthorCategoryEnum extends Enum
{
    public const COMMITTEE = 'COMMITTEE';
    public const ADHERENT = 'ADHERENT';
    public const QG = 'QG';

    public const ALL_CATEGORIES = [
        self::COMMITTEE,
        self::ADHERENT,
        self::QG,
    ];
}
