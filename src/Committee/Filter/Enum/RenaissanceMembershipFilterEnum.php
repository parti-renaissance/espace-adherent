<?php

namespace App\Committee\Filter\Enum;

use MyCLabs\Enum\Enum;

class RenaissanceMembershipFilterEnum extends Enum
{
    public const ADHERENT_OR_SYMPATHIZER_RE = 'adherent_or_sympathizer_re';
    public const ADHERENT_RE = 'adherent_re';
    public const SYMPATHIZER_RE = 'sympathizer_re';
    public const OTHERS_ADHERENT = 'others_adherent';

    public const CHOICES = [
        'renaissance.membership.all' => self::ADHERENT_OR_SYMPATHIZER_RE,
        'renaissance.membership.adherent' => self::ADHERENT_RE,
        'renaissance.membership.sympathizer' => self::SYMPATHIZER_RE,
        'renaissance.membership.none' => self::OTHERS_ADHERENT,
    ];
}
