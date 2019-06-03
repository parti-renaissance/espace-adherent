<?php

namespace AppBundle\Security\Voter\CitizenProject;

use AppBundle\CitizenProject\CitizenProjectPermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Security\Voter\AbstractAdherentVoter;

class AdministrateCitizenProjectVoter extends AbstractAdherentVoter
{
    protected function supports($attribute, $subject)
    {
        return CitizenProjectPermissions::ADMINISTRATE === $attribute && $subject instanceof CitizenProject;
    }

    /**
     * @param CitizenProject $citizenProject
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $citizenProject): bool
    {
        if (!$citizenProject->isApproved()) {
            return $citizenProject->isCreatedBy($adherent->getUuid());
        }

        return $adherent->isAdministratorOf($citizenProject);
    }
}
