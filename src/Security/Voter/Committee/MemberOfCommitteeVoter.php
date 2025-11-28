<?php

declare(strict_types=1);

namespace App\Security\Voter\Committee;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Security\Voter\AbstractAdherentVoter;

class MemberOfCommitteeVoter extends AbstractAdherentVoter
{
    private const PERMISSION = 'MEMBER_OF_COMMITTEE';

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof Committee && self::PERMISSION === $attribute;
    }

    /**
     * @param Committee $committee
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $committee): bool
    {
        if (!$committee->isApproved()) {
            return false;
        }

        return null !== $adherent->getMembershipFor($committee);
    }
}
