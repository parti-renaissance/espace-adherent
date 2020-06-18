<?php

namespace App\VotingPlatform\Election;

use MyCLabs\Enum\Enum;

class ElectionStatusEnum extends Enum
{
    public const OPEN = 'open';
    public const CLOSED = 'closed';
}
