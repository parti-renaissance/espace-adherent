<?php

namespace AppBundle\Security\Voter\Admin;

use AppBundle\Adherent\CertificationPermissions;
use AppBundle\Entity\Administrator;
use AppBundle\Entity\CertificationRequest;

class CertificationRequestProcessVoter extends AbstractAdminVoter
{
    protected function supports($attribute, $subject)
    {
        return \in_array($attribute, CertificationPermissions::REQUEST_PROCESS, true)
            && $subject instanceof CertificationRequest
        ;
    }

    /**
     * @param CertificationRequest $subject
     */
    protected function doVoteOnAttribute(string $attribute, Administrator $administrator, $subject): bool
    {
        switch ($attribute) {
            case CertificationPermissions::APPROVE:
            case CertificationPermissions::BLOCK:
                return $subject->isPending();
            case CertificationPermissions::REFUSE:
                return $subject->isPending() || $subject->isBlocked();
            default:
                return false;
        }
    }
}
