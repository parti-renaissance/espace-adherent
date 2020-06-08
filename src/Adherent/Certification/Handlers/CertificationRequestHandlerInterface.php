<?php

namespace App\Adherent\Certification\Handlers;

use App\Entity\CertificationRequest;

interface CertificationRequestHandlerInterface
{
    public function supports(CertificationRequest $certificationRequest): bool;

    public function handle(CertificationRequest $certificationRequest): void;
}
