<?php

namespace AppBundle\Security\Voter;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ApplicationRequest\ApplicationRequest;
use AppBundle\Intl\FranceCitiesBundle;

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
        $inseeCode = $adherent->getMunicipalChiefManagedArea()->getInseeCode();

        return
            \in_array($inseeCode, $subject->getFavoriteCities(), true)
            || (
                isset(FranceCitiesBundle::SPECIAL_CITY_ZONES[$inseeCode])
                && preg_match('/#'.rtrim($inseeCode, '0').'/', implode($subject->getFavoriteCityPrefixedCodes()))
            )
        ;
    }
}
