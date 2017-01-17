<?php

namespace AppBundle\Committee\Voter;

use AppBundle\Committee\CommitteePermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;

class ShowCommitteeVoter extends AbstractCommitteeVoter
{
    protected function supports($attribute, $committee)
    {
        return CommitteePermissions::SHOW === $attribute && $committee instanceof Committee;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, Committee $committee): bool
    {
        return $committee->isApproved() || $committee->isCreatedBy($adherent->getUuid());
    }
}
