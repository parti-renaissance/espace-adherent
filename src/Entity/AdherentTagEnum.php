<?php

namespace App\Entity;

use MyCLabs\Enum\Enum;

final class AdherentTagEnum extends Enum
{
    public const ELECTED = 'Élu';
    public const VERY_ACTIVE = 'Très actif';
    public const ACTIVE = 'Actif';
    public const LOW_ACTIVE = 'Peu actif';
    public const MEDIATION = 'Médiation';
    public const SUBSTITUTE = 'Suppléant';
    public const LAREM = 'LaREM';
}
