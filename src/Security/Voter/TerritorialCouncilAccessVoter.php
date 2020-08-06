<?php

namespace App\Security\Voter;

use App\Entity\Adherent;

class TerritorialCouncilAccessVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'TERRITORIAL_COUNCIL_MEMBER';

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        return $adherent->hasTerritorialCouncilMembership();
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute;
    }
}
