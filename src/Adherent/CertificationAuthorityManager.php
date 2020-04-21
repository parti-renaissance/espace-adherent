<?php

namespace AppBundle\Adherent;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Administrator;
use AppBundle\Entity\CertificationRequest;
use Doctrine\ORM\EntityManagerInterface;

class CertificationAuthorityManager
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function certify(Adherent $adherent): void
    {
        $adherent->certify();

        $this->entityManager->flush();
    }

    public function approve(CertificationRequest $certificationRequest, Administrator $administrator): void
    {
        $certificationRequest->approve();
        $certificationRequest->setProcessedBy($administrator);
        $certificationRequest->getAdherent()->certify();

        $this->entityManager->flush();
    }

    public function refuse(CertificationRequest $certificationRequest, Administrator $administrator): void
    {
        $certificationRequest->refuse();
        $certificationRequest->setProcessedBy($administrator);

        $this->entityManager->flush();
    }
}
