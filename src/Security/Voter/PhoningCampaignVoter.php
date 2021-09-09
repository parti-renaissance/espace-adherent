<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\Phoning\Campaign;

class PhoningCampaignVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_MANAGE_PHONING_CAMPAIGN';

    /** @param Campaign $subject */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if (!$team = $subject->getTeam()) {
            return false;
        }

        return $team->hasAdherent($adherent);
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof Campaign;
    }
}
