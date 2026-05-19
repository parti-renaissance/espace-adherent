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
    public const string QUESTIONNAIRE_TERRAIN = 'questionnaire_terrain';

    public const array LABELS = [
        self::PAP => 'porte à porte',
        self::BOITAGE => 'boitage',
        self::TRACTAGE => 'tractage',
        self::COLLAGE => 'collage',
        self::QUESTIONNAIRE_TERRAIN => 'questionnaire terrain',
    ];

    public const array EMOJIS = [
        self::PAP => '🚪',
        self::BOITAGE => '📬',
        self::TRACTAGE => '📄',
        self::COLLAGE => '🖌️',
        self::QUESTIONNAIRE_TERRAIN => '📋',
    ];
}
