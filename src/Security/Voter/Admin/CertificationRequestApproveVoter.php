<?php

namespace AppBundle\Security\Voter\Admin;

use AppBundle\Adherent\CertificationPermissions;
use AppBundle\Entity\Administrator;
use AppBundle\Entity\CertificationRequest;

class CertificationRequestApproveVoter extends AbstractAdminVoter
{
    protected function supports($attribute, $subject)
    {
        return CertificationPermissions::APPROVE === $attribute && $subject instanceof CertificationRequest;
    }

    protected function doVoteOnAttribute(string $attribute, Administrator $administrator, $subject): bool
    {
        return $subject->isPending();
    }
}
