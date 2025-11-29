<?php

declare(strict_types=1);

namespace App\VotingPlatform\Election;

use MyCLabs\Enum\Enum;

class VoteCommandStateEnum extends Enum
{
    public const INITIALIZE = 'initialize';
    public const VOTE = 'vote';
    public const CONFIRM = 'confirm';
    public const FINISH = 'finish';

    public const TO_VOTE = 'to_vote';
    public const TO_CONFIRM = 'to_confirm';
    public const TO_FINISH = 'to_finish';
}
