<?php

namespace App\Security\Voter\Committee;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Security\Voter\AbstractAdherentVoter;

class ChangeCommitteeVoteVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'ABLE_TO_CHANGE_COMMITTEE_VOTE';

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if (($subject instanceof Committee) && null === $adherent->getMembershipFor($subject)) {
            return false;
        }

        if ($adherent->isSupervisor()) {
            return false;
        }

        if ($adherent->getMemberships()->getCommitteeCandidacyMembership()) {
            return false;
        }

        return true;
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute;
    }
}
