<?php

namespace App\NationalEvent;

use MyCLabs\Enum\Enum;

class InscriptionStatusEnum extends Enum
{
    public const PENDING = 'pending';
    public const ACCEPTED = 'accepted';
    public const INCONCLUSIVE = 'inconclusive';
    public const REFUSED = 'refused';
    public const DUPLICATE = 'duplicate';
}
