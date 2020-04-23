<?php

namespace AppBundle\Security\Voter\Admin;

use AppBundle\Adherent\CertificationPermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Administrator;

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
