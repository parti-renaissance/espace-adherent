<?php

namespace AppBundle\Security\Voter\Committee;

use AppBundle\Committee\CommitteePermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Security\Voter\AbstractAdherentVoter;

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
        if (!$committee->isApproved()) {
            return $adherent->isSupervisorOf($committee) || $committee->isCreatedBy($adherent->getUuid());
        }

        return $adherent->isHostOf($committee);
    }
}
