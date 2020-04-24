<?php

namespace AppBundle\Security\Voter;

use AppBundle\Adherent\CertificationPermissions;
use AppBundle\Entity\Adherent;

class CertificationVoter extends AbstractAdherentVoter
{
    protected function supports($attribute, $subject)
    {
        return \in_array($attribute, [CertificationPermissions::CERTIFIED, CertificationPermissions::REQUEST], true)
            && null === $subject
        ;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        switch ($attribute) {
            case CertificationPermissions::CERTIFIED:
                return $adherent->isCertified();
            case CertificationPermissions::REQUEST:
                if ($adherent->isCertified()) {
                    return false;
                }

                $certificationRequests = $adherent->getCertificationRequests();

                return !$certificationRequests->hasBlockedCertificationRequest()
                    && !$certificationRequests->hasPendingCertificationRequest()
                ;
            default:
                return false;
        }
    }
}
