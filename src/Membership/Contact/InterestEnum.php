<?php

declare(strict_types=1);

namespace App\Membership\Contact;

class InterestEnum
{
    public const ACTION_TERRAIN = 'action_terrain';
    public const CAMPAGNE_NUMERIQUE = 'campagne_numerique';
    public const PROCHES = 'proches';

    public const ALL = [
        self::ACTION_TERRAIN,
        self::CAMPAGNE_NUMERIQUE,
        self::PROCHES,
    ];
}
