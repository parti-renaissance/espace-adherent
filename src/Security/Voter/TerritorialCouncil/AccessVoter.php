<?php

namespace App\Security\Voter\TerritorialCouncil;

use App\Entity\Adherent;
use App\Security\Voter\AbstractAdherentVoter;

class AccessVoter extends AbstractAdherentVoter
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
