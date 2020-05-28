<?php

namespace App\Security\Voter\Admin;

use App\Adherent\Certification\CertificationPermissions;
use App\Entity\Adherent;
use App\Entity\Administrator;

class AdherentUncertifyVoter extends AbstractAdminVoter
{
    protected function supports($attribute, $subject)
    {
        return CertificationPermissions::UNCERTIFY === $attribute && $subject instanceof Adherent;
    }

    /**
     * @param Adherent $subject
     */
    protected function doVoteOnAttribute(string $attribute, Administrator $administrator, $subject): bool
    {
        return $subject->isCertified();
    }
}
