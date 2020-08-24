<?php

namespace App\Security\Voter\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Security\Voter\AbstractAdherentVoter;

class AccessVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'TERRITORIAL_COUNCIL_MEMBER';

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $membership = $adherent->getTerritorialCouncilMembership();

        if ($membership && $subject instanceof TerritorialCouncil) {
            return $membership->getTerritorialCouncil() === $subject;
        }

        return $membership instanceof TerritorialCouncilMembership;
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute;
    }
}
