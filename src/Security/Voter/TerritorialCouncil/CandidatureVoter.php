<?php

namespace App\Security\Voter\TerritorialCouncil;

use App\Entity\Adherent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CandidatureVoter extends Voter
{
    public const PERMISSION = 'ABLE_TO_BECOME_TERRITORIAL_COUNCIL_CANDIDATE';

    protected function voteOnAttribute($attribute, $adherent, TokenInterface $token)
    {
        /** @var Adherent $adherent */
        if (!$adherent->hasTerritorialCouncilMembership()) {
            return false;
        }

        $membership = $adherent->getTerritorialCouncilMembership();
        $election = $membership->getTerritorialCouncil()->getCurrentElection();

        if (!$election) {
            return false;
        }

        $candidacy = $membership->getCandidacyForElection($election);

        if ($candidacy && $candidacy->isConfirmed()) {
            return false;
        }

        if ($membership->hasForbiddenForCandidacyQuality()) {
            return false;
        }

        return true;
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof Adherent;
    }
}
