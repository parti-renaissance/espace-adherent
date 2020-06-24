<?php

namespace App\Security\Voter;

use App\Adherent\Certification\CertificationPermissions;
use App\Entity\Adherent;

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
                if ($adherent->isCertified() || empty($adherent->getBirthdate())) {
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
