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
        /** @var VotePlace $subject */
        if ($adherent->isReferent()) {
            return (bool) array_intersect(
                $adherent->getManagedAreaTagCodes(),
                array_merge(
                    [$subject->getCountry()],
                    Address::FRANCE === $subject->getCountry() ? [substr($subject->getPostalCode(), 0, 2)] : []
                )
            );
        }

        if ($adherent->isMunicipalChief()) {
            return $subject->getInseeCode() === $adherent->getMunicipalChiefManagedArea()->getInseeCode();
        }

        return false;
    }

    protected function supports($attribute, $subject)
    {
        return self::MANAGE_VOTE_PLACE === $attribute && $subject instanceof VotePlace;
    }
}
