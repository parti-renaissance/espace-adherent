<?php

namespace AppBundle\CitizenProject\Voter;

use AppBundle\CitizenProject\CitizenProjectPermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;

class CommentCitizenProjectVoter extends AbstractCitizenProjectVoter
{
    protected function supports($attribute, $citizenProject)
    {
        return in_array($attribute, [CitizenProjectPermissions::COMMENT, CitizenProjectPermissions::SHOW_COMMENT], true)
               && $citizenProject instanceof CitizenProject;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, CitizenProject $citizenProject): bool
    {
        return null !== $adherent->getCitizenProjectMembershipFor($citizenProject);
    }
}
