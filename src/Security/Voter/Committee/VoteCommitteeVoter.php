<?php

namespace AppBundle\Security\Voter\Committee;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Security\Voter\AbstractAdherentVoter;

class VoteCommitteeVoter extends AbstractAdherentVoter
{
    private const PERMISSION = 'VOTE_COMMITTEE';

    protected function supports($attribute, $subject)
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
