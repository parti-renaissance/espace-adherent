<?php

namespace App\Mailer\Message\Renaissance\VotingPlatform;

final class VoteReminder1HMessage extends AbstractVoteReminderMessage
{
    protected static function generateSubject(): string
    {
        return 'Dans une heure il sera trop tard';
    }
}
