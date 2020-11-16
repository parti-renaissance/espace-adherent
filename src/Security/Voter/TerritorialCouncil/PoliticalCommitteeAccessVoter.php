<?php

namespace App\Security\Voter\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\PoliticalCommitteeMembership;
use App\Security\Voter\AbstractAdherentVoter;

class PoliticalCommitteeAccessVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'POLITICAL_COMMITTEE_MEMBER';

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $membership = $adherent->getPoliticalCommitteeMembership();

        if (!$membership instanceof PoliticalCommitteeMembership) {
            return false;
        }

        $politicalCommittee = $membership->getPoliticalCommittee();
        if (!$politicalCommittee->isActive()) {
            return false;
        }

        return true;
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute;
    }
}
