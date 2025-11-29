<?php

declare(strict_types=1);

namespace App\VotingPlatform\Designation;

use MyCLabs\Enum\Enum;

class MajorityVoteMentionEnum extends Enum
{
    public const EXCELLENT = 'excellent';
    public const VERY_GOOD = 'very_good';
    public const GOOD = 'good';
    public const FAIR = 'fair';
    public const INSUFFICIENT = 'insufficient';

    public const ALL = [
        self::EXCELLENT,
        self::VERY_GOOD,
        self::GOOD,
        self::FAIR,
        self::INSUFFICIENT,
    ];
}
