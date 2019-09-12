<?php

namespace AppBundle\Security\Voter;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ApplicationRequest\ApplicationRequest;

class MunicipalChiefVoter extends AbstractAdherentVoter
{
    public const ROLE = 'MUNICIPAL_CHIEF_OF';

    protected function supports($attribute, $subject)
    {
        return self::ROLE === $attribute && $subject instanceof ApplicationRequest;
    }

    /**
     * @param ApplicationRequest $subject
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        return \in_array(
            $adherent->getMunicipalChiefManagedArea()->getInseeCode(),
            $subject->getFavoriteCities(),
            true
        );
    }
}
