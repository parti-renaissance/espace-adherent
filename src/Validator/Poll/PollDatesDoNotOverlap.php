<?php

declare(strict_types=1);

namespace App\Validator\Poll;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class PollDatesDoNotOverlap extends Constraint
{
    public const int MIN_GAP_HOURS = 6;

    public string $message = 'poll.dates.overlap';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
