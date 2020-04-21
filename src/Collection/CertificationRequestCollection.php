<?php

namespace AppBundle\Collection;

use AppBundle\Entity\CertificationRequest;
use Doctrine\Common\Collections\ArrayCollection;

class CertificationRequestCollection extends ArrayCollection
{
    public function getPendingCertificationRequest(): ?CertificationRequest
    {
        $certificationRequest = $this
            ->filter(function (CertificationRequest $certificationRequest) {
                return $certificationRequest->isPending();
            })
            ->first()
        ;

        return $certificationRequest ? $certificationRequest : null;
    }

    public function hasPendingCertificationRequest(): bool
    {
        return null !== $this->getPendingCertificationRequest();
    }

    public function getRefusedCertificationRequests(): self
    {
        return $this->filter(function (CertificationRequest $certificationRequest) {
            return $certificationRequest->isRefused();
        });
    }

    public function hasRefusedCertificationRequest(): bool
    {
        return !$this->getRefusedCertificationRequests()->isEmpty();
    }
}
