<?php

namespace App\Mailer\Message;

abstract class AbstractVotingPlatformCandidacyInvitationMessage extends Message
{
    protected static function getMailSubjectPrefix(bool $isCommittee): string
    {
        return $isCommittee ? '[Élections internes]' : '[Désignations]';
    }
}
