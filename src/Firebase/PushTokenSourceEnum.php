<?php

namespace App\Firebase;

use MyCLabs\Enum\Enum;

class PushTokenSourceEnum extends Enum
{
    public const JE_MARCHE = 'je_marche';

    public const ALL = [
        self::JE_MARCHE,
    ];
}
