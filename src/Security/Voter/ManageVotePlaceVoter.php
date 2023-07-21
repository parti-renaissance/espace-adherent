<?php

namespace App\Security\Voter;

use App\Address\AddressInterface;
use App\Entity\Adherent;
use App\Entity\Election\VotePlace;

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
                    AddressInterface::FRANCE === $subject->getCountry() ? [substr($subject->getPostalCode(), 0, 2)] : []
                )
            );
        }

        return $result;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::MANAGE_VOTE_PLACE === $attribute && $subject instanceof VotePlace;
    }
}
