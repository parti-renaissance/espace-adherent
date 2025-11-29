<?php

declare(strict_types=1);

namespace App\Membership;

use MyCLabs\Enum\Enum;

final class MembershipSourceEnum extends Enum
{
    public const JEMENGAGE = 'jemengage';
    public const AVECVOUS = 'avecvous';
    public const PLATFORM = 'platform';
    public const RENAISSANCE = 'renaissance';
    public const BESOIN_D_EUROPE = 'besoindeurope';
    public const LEGISLATIVE = 'legislative';
}
