<?php

declare(strict_types=1);

namespace App\Security\Voter\Admin;

use App\Adherent\Certification\CertificationPermissions;
use App\Entity\Adherent;
use App\Entity\Administrator;

class AdherentCertifyVoter extends AbstractAdminVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return CertificationPermissions::CERTIFY === $attribute && $subject instanceof Adherent;
    }

    /**
     * @param Adherent $subject
     */
    protected function doVoteOnAttribute(string|int $attribute, Administrator $administrator, $subject): bool
    {
        return !$subject->isCertified();
    }
}
