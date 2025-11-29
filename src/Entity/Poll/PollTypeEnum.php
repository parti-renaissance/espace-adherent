<?php

declare(strict_types=1);

namespace App\Entity\Poll;

use MyCLabs\Enum\Enum;

final class PollTypeEnum extends Enum
{
    public const LOCAL = 'local';
    public const NATIONAL = 'national';
}
