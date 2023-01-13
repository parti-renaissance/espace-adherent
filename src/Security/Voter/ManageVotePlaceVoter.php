<?php

namespace App\Security\Voter;

use App\Address\Address;
use App\Entity\Adherent;
use App\Entity\VotePlace;
use App\Intl\FranceCitiesBundle;

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
            $result =
                $subject->getInseeCode() === ($inseeCode = $adherent->getMunicipalChiefManagedArea()->getInseeCode())
                || (
                    isset(FranceCitiesBundle::SPECIAL_CITY_DISTRICTS[$inseeCode])
                    && \array_key_exists($subject->getInseeCode(), FranceCitiesBundle::SPECIAL_CITY_DISTRICTS[$inseeCode])
                )
            ;
        }

        return $result;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::MANAGE_VOTE_PLACE === $attribute && $subject instanceof VotePlace;
    }
}
