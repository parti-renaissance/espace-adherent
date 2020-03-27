<?php

namespace AppBundle\Security\Voter\Committee;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Security\Voter\AbstractAdherentVoter;

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
