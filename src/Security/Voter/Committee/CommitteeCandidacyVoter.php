<?php

namespace App\Security\Voter\Committee;

use App\Entity\Adherent;
use App\Security\Voter\AbstractAdherentVoter;

class CommitteeCandidacyVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'ABLE_TO_CANDIDATE';

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if (
            $adherent->isReferent()
            || $adherent->isSupervisor()
            || $adherent->isDeputy()
            || $adherent->isSenator()
        ) {
            return false;
        }

        return true;
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute;
    }
}
