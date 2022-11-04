<?php

namespace App\Security\Voter\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CandidatureVoter extends Voter
{
    public const PERMISSION = 'ABLE_TO_BECOME_TERRITORIAL_COUNCIL_CANDIDATE';

    protected function voteOnAttribute($attribute, $adherent, TokenInterface $token): bool
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
        return self::PERMISSION === $attribute && $subject instanceof Adherent;
    }
}
