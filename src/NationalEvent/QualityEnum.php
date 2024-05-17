<?php

namespace App\NationalEvent;

use MyCLabs\Enum\Enum;

class QualityEnum extends Enum
{
    public const COLISTIER = 'colistier';
    public const PARLEMENTAIRE = 'parlementaire';

    public const LABELS = [
        self::COLISTIER => 'Colistier(e) Besoin d\'Europe',
        self::PARLEMENTAIRE => 'Parlementaire',
    ];
}
