<?php

declare(strict_types=1);

namespace App\Mailer\Message;

use App\Entity\VotingPlatform\Designation\Designation;
use App\VotingPlatform\Designation\DesignationTypeEnum;

abstract class AbstractVotingPlatformMessage extends Message
{
    protected static function getMailSubjectPrefix(Designation $designation, bool $isPartial = false): string
    {
        if (DesignationTypeEnum::COMMITTEE_SUPERVISOR === $designation->getType()) {
            return \sprintf('%ss %s', $designation->getDenomination(false, true), $isPartial ? 'partielles' : 'internes');
        }

        return \sprintf('%ss%s', $designation->getDenomination(false, true), $isPartial ? ' partielles' : '');
    }
}
