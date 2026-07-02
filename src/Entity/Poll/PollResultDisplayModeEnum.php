<?php

declare(strict_types=1);

namespace App\Entity\Poll;

enum PollResultDisplayModeEnum: string
{
    case AFTER_VOTE = 'after_vote';
    case AFTER_POLL = 'after_poll';
    case NEVER = 'never';

    public function getLabel(): string
    {
        return match ($this) {
            self::AFTER_VOTE => 'Immédiatement après vote',
            self::AFTER_POLL => 'Après la fin du sondage',
            self::NEVER => 'Jamais',
        };
    }
}
