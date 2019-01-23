<?php

namespace AppBundle\Entity\IdeasWorkshop;

final class AuthorCategoryEnum
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
