<?php

namespace AppBundle\Security\Voter;

use AppBundle\Adherent\CertificationPermissions;
use AppBundle\Entity\Adherent;

class CertificationRequestVoter extends AbstractAdherentVoter
{
    protected function supports($attribute, $subject)
    {
        return CertificationPermissions::REQUEST === $attribute && null === $subject;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if ($adherent->isCertified()) {
            return false;
        }

        $certificationRequests = $adherent->getCertificationRequests();

        return !$certificationRequests->hasBlockedCertificationRequest()
            && !$certificationRequests->hasPendingCertificationRequest()
        ;
    }
}
