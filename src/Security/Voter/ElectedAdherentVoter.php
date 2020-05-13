<?php

namespace App\Security\Voter;

use App\Adherent\AdherentRoleEnum;
use App\Entity\Adherent;

class ElectedAdherentVoter extends AbstractAdherentVoter
{
    protected function supports($attribute, $subject)
    {
        return AdherentRoleEnum::ELECTED === $attribute;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        return $adherent->isElected();
    }
}
