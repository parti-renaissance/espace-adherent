<?php

namespace App\Security\Voter\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Security\Voter\AbstractAdherentVoter;
use App\VotingPlatform\Designation\DesignationTypeEnum;

class CandidatureVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'ABLE_TO_BECOME_TERRITORIAL_COUNCIL_CANDIDATE';

    protected function doVoteOnAttribute($attribute, $adherent, $subject): bool
    {
        /** @var Adherent $adherent */
        if (!$adherent->hasTerritorialCouncilMembership()) {
            return false;
        }

        $membership = $adherent->getTerritorialCouncilMembership();

        if (!$membership->getTerritorialCouncil()->getCurrentElection()) {
            return false;
        }

        if (!$membership->getAvailableForCandidacyQualityNames()) {
            return false;
        }

        if ($membership->hasForbiddenForCandidacyQuality()) {
            return false;
        }

        if (DesignationTypeEnum::COPOL === $membership->getTerritorialCouncil()->getCurrentElection()->getDesignationType()) {
            if ($adherent->hasPoliticalCommitteeMembership() && $adherent->getPoliticalCommitteeMembership()->hasOneOfQualities([
                TerritorialCouncilQualityEnum::MAYOR,
                TerritorialCouncilQualityEnum::LEADER,
            ])) {
                return false;
            }
        }

        return true;
    }

    protected function supports($attribute, $subject): bool
    {
        return self::PERMISSION === $attribute;
    }
}
