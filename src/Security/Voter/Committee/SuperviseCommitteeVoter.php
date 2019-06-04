<?php

namespace AppBundle\Security\Voter\Committee;

use AppBundle\Committee\CommitteePermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Security\Voter\AbstractAdherentVoter;

class SuperviseCommitteeVoter extends AbstractAdherentVoter
{
    protected function supports($attribute, $subject): bool
    {
        return CommitteePermissions::SUPERVISE === $attribute && $subject instanceof Committee;
    }

    /**
     * @param Committee $committee
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $committee): bool
    {
        return $adherent->isSupervisorOf($committee);
    }
}
