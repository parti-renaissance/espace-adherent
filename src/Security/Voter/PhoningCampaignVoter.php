<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\Phoning\Campaign;

class PhoningCampaignVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_MANAGE_PHONING_CAMPAIGN';

    /** @param Campaign $subject */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        return (bool) $subject->getTeam()?->hasAdherent($adherent);
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION === $attribute && $subject instanceof Campaign;
    }
}
