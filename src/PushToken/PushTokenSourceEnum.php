<?php

namespace App\PushToken;

use MyCLabs\Enum\Enum;

class PushTokenSourceEnum extends Enum
{
    public const JE_MARCHE = 'je_marche';
    public const VOX = 'vox';

    public const ALL = [
        self::JE_MARCHE,
        self::VOX,
    ];
}
