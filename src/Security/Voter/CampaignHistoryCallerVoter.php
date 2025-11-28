<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\Phoning\CampaignHistory;

class CampaignHistoryCallerVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'IS_CAMPAIGN_HISTORY_CALLER';

    /** @param CampaignHistory $subject */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        return $subject->getCaller() === $adherent;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION === $attribute && $subject instanceof CampaignHistory;
    }
}
