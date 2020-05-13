<?php

namespace App\Security\Voter;

use App\Adherent\AdherentRoleEnum;
use App\Entity\Adherent;

class LaREMAdherentVoter extends AbstractAdherentVoter
{
    protected function supports($attribute, $subject)
    {
        return AdherentRoleEnum::LAREM === $attribute;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        return $adherent->isLaREM();
    }
}
