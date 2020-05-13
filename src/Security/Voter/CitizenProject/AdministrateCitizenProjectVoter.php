<?php

namespace App\Security\Voter\CitizenProject;

use App\CitizenProject\CitizenProjectPermissions;
use App\Entity\Adherent;
use App\Entity\CitizenProject;
use App\Security\Voter\AbstractAdherentVoter;

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
