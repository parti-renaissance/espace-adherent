<?php

namespace App\Committee\Filter\Enum;

use MyCLabs\Enum\Enum;

class RenaissanceMembershipFilterEnum extends Enum
{
    public const ADHERENT_RE = 'adherent_re';
    public const SYMPATHIZER_RE = 'sympathizer_re';
    public const OTHERS_ADHERENT = 'others_adherent';

    public const CHOICES = [
        'adherent.renaissance.membership' => self::ADHERENT_RE,
        'adherent.renaissance.sympathizer' => self::SYMPATHIZER_RE,
        'common.adherent.others' => self::OTHERS_ADHERENT,
    ];
}
