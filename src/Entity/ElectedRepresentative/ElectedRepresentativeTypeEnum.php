<?php

namespace App\Entity\ElectedRepresentative;

use MyCLabs\Enum\Enum;

final class ElectedRepresentativeTypeEnum extends Enum
{
    public const ADHERENT = 'adherent';
    public const CONTACT = 'contact';
    public const OTHER = 'other';

    public const ALL = [
        self::ADHERENT,
        self::CONTACT,
        self::OTHER,
    ];
}
