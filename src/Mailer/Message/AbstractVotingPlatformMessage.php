<?php

namespace App\Mailer\Message;

use App\Entity\VotingPlatform\Designation\Designation;
use App\VotingPlatform\Designation\DesignationTypeEnum;

abstract class AbstractVotingPlatformMessage extends Message
{
    protected static function getMailSubjectPrefix(Designation $designation, bool $isPartial = false): string
    {
        if (DesignationTypeEnum::COMMITTEE_SUPERVISOR === $designation->getType()) {
            return sprintf('Élections %s', $isPartial ? 'partielles' : 'internes');
        }

        return sprintf('Désignations%s', $isPartial ? ' partielles' : '');
    }
}
