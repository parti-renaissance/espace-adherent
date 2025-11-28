<?php

declare(strict_types=1);

namespace App\Security\Voter\Committee;

use App\Committee\CommitteePermissionEnum;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Security\Voter\AbstractAdherentVoter;

class CompletelySuperviseCommitteeVoter extends AbstractAdherentVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return CommitteePermissionEnum::MANAGE_DESIGNATIONS === $attribute && $subject instanceof Committee;
    }

    /**
     * @param Committee $committee
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $committee): bool
    {
        return $adherent->isSupervisorOf($committee, false);
    }
}
