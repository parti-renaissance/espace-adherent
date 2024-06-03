<?php

namespace App\Action;

use MyCLabs\Enum\Enum;

class ActionTypeEnum extends Enum
{
    public const string PAP = 'pap';
    public const string BOITAGE = 'boitage';
    public const string TRACTAGE = 'tractage';
    public const string COLLAGE = 'collage';

    public const array LABELS = [
        self::PAP => 'Porte à porte',
        self::BOITAGE => 'Boitage',
        self::TRACTAGE => 'Tractage',
        self::COLLAGE => 'Collage',
    ];

    public const array EMOJIS = [
        self::PAP => '🚪',
        self::BOITAGE => '📬',
        self::TRACTAGE => '📄',
        self::COLLAGE => '🖌️',
    ];
}
