<?php

namespace App\Adherent\Certification;

use App\Entity\Administrator;
use App\Entity\CertificationRequest;
use Symfony\Component\Validator\Constraints as Assert;

abstract class CertificationRequestModerationCommand
{
    private $certificationRequest;
    private $administrator;

    /**
     * @var string|null
     *
     * @Assert\Length(max=500)
     */
    private $comment;

    public function __construct(CertificationRequest $certificationRequest, Administrator $administrator)
    {
        $this->certificationRequest = $certificationRequest;
        $this->administrator = $administrator;
    }

    public function getCertificationRequest(): CertificationRequest
    {
        return $this->certificationRequest;
    }

    public function getAdministrator(): Administrator
    {
        return $this->administrator;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }
}
