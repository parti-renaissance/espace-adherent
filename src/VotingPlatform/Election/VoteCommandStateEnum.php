<?php

namespace AppBundle\VotingPlatform\Election;

use MyCLabs\Enum\Enum;

class VoteCommandStateEnum extends Enum
{
    public const INITIALIZE = 'initialize';
    public const START = 'start';
    public const VOTE = 'vote';
    public const CONFIRM = 'confirm';
    public const FINISH = 'finish';

    public const TO_START = 'to_start';
    public const TO_VOTE = 'to_vote';
    public const TO_CONFIRM = 'to_confirm';
    public const TO_FINISH = 'to_finish';
}
