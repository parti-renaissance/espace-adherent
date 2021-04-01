<?php

namespace App\Entity\Poll;

use MyCLabs\Enum\Enum;

final class PollTypeEnum extends Enum
{
    public const LOCAL = 'local';
    public const NATIONAL = 'national';
}
