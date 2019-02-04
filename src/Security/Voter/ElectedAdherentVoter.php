<?php

namespace AppBundle\Security\Voter;

use AppBundle\Adherent\AdherentRoleEnum;
use AppBundle\Entity\Adherent;

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
