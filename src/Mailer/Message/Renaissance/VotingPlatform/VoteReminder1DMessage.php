<?php

namespace App\Mailer\Message\Renaissance\VotingPlatform;

final class VoteReminder1DMessage extends AbstractVoteReminderMessage
{
    protected static function generateSubject(): string
    {
        return 'Dernier jour pour participer';
    }
}
