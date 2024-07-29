<?php

namespace App\Mailer\Message\Renaissance\VotingPlatform;

use App\Entity\VotingPlatform\Designation\Designation;
use App\Mailer\Message\Renaissance\AbstractRenaissanceMessage;

abstract class AbstractRenaissanceVotingPlatformMessages extends AbstractRenaissanceMessage
{
    protected static function getMailSubjectPrefix(Designation $designation, bool $isPartial = false): string
    {
        return \sprintf('%ss%s', $designation->getDenomination(false, true), $isPartial ? ' partielles' : '');
    }
}
