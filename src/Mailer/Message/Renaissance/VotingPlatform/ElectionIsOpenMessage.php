<?php

namespace App\Mailer\Message\Renaissance\VotingPlatform;

class ElectionIsOpenMessage extends AbstractIsOpenMessage
{
    protected static function generateSubject(): string
    {
        return 'L\'élection est ouverte';
    }
}
