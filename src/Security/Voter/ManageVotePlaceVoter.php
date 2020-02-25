<?php

namespace AppBundle\Security\Voter;

use AppBundle\Address\Address;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\VotePlace;

class ManageVotePlaceVoter extends AbstractAdherentVoter
{
    public const MANAGE_VOTE_PLACE = 'MANAGE_VOTE_PLACE';

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $result = false;

        /** @var VotePlace $subject */
        if ($adherent->isReferent()) {
            $result = (bool) array_intersect(
                $adherent->getManagedAreaTagCodes(),
                array_merge(
                    [$subject->getCountry()],
                    Address::FRANCE === $subject->getCountry() ? [substr($subject->getPostalCode(), 0, 2)] : []
                )
            );
        }

        if (false === $result && $adherent->isMunicipalChief()) {
            $result = $subject->getInseeCode() === $adherent->getMunicipalChiefManagedArea()->getInseeCode();
        }

        if (false === $result && $adherent->isMunicipalManager()) {
            $result = (bool) array_intersect($subject->getPostalCodesAsArray(), $adherent->getMunicipalManagerRole()->getPostalCodes());
        }

        return $result;
    }

    protected function supports($attribute, $subject)
    {
        return self::MANAGE_VOTE_PLACE === $attribute && $subject instanceof VotePlace;
    }
}
