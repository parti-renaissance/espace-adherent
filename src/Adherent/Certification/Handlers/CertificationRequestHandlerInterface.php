<?php

declare(strict_types=1);

namespace App\Adherent\Certification\Handlers;

use App\Entity\CertificationRequest;

interface CertificationRequestHandlerInterface
{
    public function getPriority(): int;

    public function supports(CertificationRequest $certificationRequest): bool;

    public function handle(CertificationRequest $certificationRequest): void;
}
