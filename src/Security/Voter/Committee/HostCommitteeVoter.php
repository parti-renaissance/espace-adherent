<?php

declare(strict_types=1);

namespace App\Security\Voter\Committee;

use App\Committee\CommitteePermissionEnum;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Security\Voter\AbstractAdherentVoter;

class HostCommitteeVoter extends AbstractAdherentVoter
{
    protected function supports(string $attribute, $committee): bool
    {
        return CommitteePermissionEnum::HOST === $attribute && $committee instanceof Committee;
    }

    /**
     * @param Committee $committee
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $committee): bool
    {
        return !$committee->isBlocked() && ($adherent->isSupervisorOf($committee) || $adherent->isHostOf($committee));
    }
}
