<?php

namespace App\Security\Voter\Committee;

use App\Committee\CommitteePermissions;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Security\Voter\AbstractAdherentVoter;

class HostCommitteeVoter extends AbstractAdherentVoter
{
    protected function supports($attribute, $committee)
    {
        return CommitteePermissions::HOST === $attribute && $committee instanceof Committee;
    }

    /**
     * @param Committee $committee
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $committee): bool
    {
        return !$committee->isBlocked() && ($adherent->isSupervisorOf($committee) || $adherent->isHostOf($committee));
    }
}
