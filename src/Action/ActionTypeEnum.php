<?php

declare(strict_types=1);

namespace App\Action;

use MyCLabs\Enum\Enum;

class ActionTypeEnum extends Enum
{
    public const string PAP = 'pap';
    public const string BOITAGE = 'boitage';
    public const string TRACTAGE = 'tractage';
    public const string COLLAGE = 'collage';

    public const array LABELS = [
        self::PAP => 'porte à porte',
        self::BOITAGE => 'boitage',
        self::TRACTAGE => 'tractage',
        self::COLLAGE => 'collage',
    ];

    public const array EMOJIS = [
        self::PAP => '🚪',
        self::BOITAGE => '📬',
        self::TRACTAGE => '📄',
        self::COLLAGE => '🖌️',
    ];
}
