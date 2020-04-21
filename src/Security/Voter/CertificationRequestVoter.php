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
        return !$adherent->isCertified()
            && !$adherent->getCertificationRequests()->hasPendingCertificationRequest()
        ;
    }
}
