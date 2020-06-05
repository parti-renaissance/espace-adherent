<?php

namespace App\Collection;

use App\Entity\CertificationRequest;
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
        $certificationRequests = $this->filter(function (CertificationRequest $certificationRequest) {
            return $certificationRequest->isRefused();
        })->toArray();

        usort($certificationRequests, function (CertificationRequest $request1, CertificationRequest $request2) {
            return $request2->getId() <=> $request1->getId();
        });

        return new self($certificationRequests);
    }

    public function hasRefusedCertificationRequest(): bool
    {
        return !$this->getRefusedCertificationRequests()->isEmpty();
    }

    public function getBlockedCertificationRequest(): ?CertificationRequest
    {
        $certificationRequest = $this
            ->filter(function (CertificationRequest $certificationRequest) {
                return $certificationRequest->isBlocked();
            })
            ->first()
        ;

        return $certificationRequest ? $certificationRequest : null;
    }

    public function hasBlockedCertificationRequest(): bool
    {
        return null !== $this->getBlockedCertificationRequest();
    }
}
