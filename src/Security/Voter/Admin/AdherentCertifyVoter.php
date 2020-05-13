<?php

namespace App\Security\Voter\Admin;

use App\Adherent\CertificationPermissions;
use App\Entity\Adherent;
use App\Entity\Administrator;

class AdherentCertifyVoter extends AbstractAdminVoter
{
    protected function supports($attribute, $subject)
    {
        return CertificationPermissions::CERTIFY === $attribute && $subject instanceof Adherent;
    }

    /**
     * @param Adherent $subject
     */
    protected function doVoteOnAttribute(string $attribute, Administrator $administrator, $subject): bool
    {
        return !$subject->isCertified();
    }
}
