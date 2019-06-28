<?php

namespace AppBundle\Security\Voter;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ApplicationRequest\ApplicationRequest;

class MunicipalChiefVoter extends AbstractAdherentVoter
{
    private const ROLE = 'MUNICIPAL_CHIEF_OF';

    protected function supports($attribute, $subject)
    {
        return self::ROLE === $attribute && $subject instanceof ApplicationRequest;
    }

    /**
     * @param ApplicationRequest $subject
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        return !empty(array_intersect(
            $subject->getFavoriteCities(),
            $adherent->getMunicipalChiefManagedArea()->getCodes()
        ));
    }
}
