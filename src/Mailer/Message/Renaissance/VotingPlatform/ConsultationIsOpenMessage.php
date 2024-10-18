<?php

namespace App\Mailer\Message\Renaissance\VotingPlatform;

class ConsultationIsOpenMessage extends AbstractIsOpenMessage
{
    protected static function generateSubject(): string
    {
        return 'La consultation est ouverte';
    }
}
