<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance\VotingPlatform;

class VoteIsOpenMessage extends AbstractIsOpenMessage
{
    protected static function generateSubject(): string
    {
        return 'Le vote est ouvert';
    }
}
