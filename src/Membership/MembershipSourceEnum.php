<?php

namespace App\Membership;

use MyCLabs\Enum\Enum;

final class MembershipSourceEnum extends Enum
{
    public const COALITIONS = 'coalitions';
    public const JEMENGAGE = 'jemengage';
    public const PLATFORM = 'platform';
}
