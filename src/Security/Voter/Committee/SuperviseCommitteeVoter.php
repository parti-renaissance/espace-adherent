<?php

namespace App\Security\Voter\Committee;

use App\Committee\CommitteePermissionEnum;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Security\Voter\AbstractAdherentVoter;

class SuperviseCommitteeVoter extends AbstractAdherentVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return CommitteePermissionEnum::SUPERVISE === $attribute && $subject instanceof Committee;
    }

    /**
     * @param Committee $committee
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $committee): bool
    {
        return !$committee->isBlocked() && $adherent->isSupervisorOf($committee);
    }
}
