<?php

declare(strict_types=1);

namespace App\Validator\Poll;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class PollVotedChoiceCannotBeRemoved extends Constraint
{
    public string $message = 'poll.choice.removed_with_votes';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
