<?php

namespace App\Poll;

use MyCLabs\Enum\Enum;

class PollSpaceEnum extends Enum
{
    public const CANDIDATE_SPACE = 'candidate';
    public const REFERENT_SPACE = 'referent';
}
