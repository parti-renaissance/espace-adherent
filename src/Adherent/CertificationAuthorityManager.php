<?php

namespace App\Adherent;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\CertificationRequest;
use App\Entity\Reporting\AdherentCertificationHistory;
use Doctrine\ORM\EntityManagerInterface;

class CertificationAuthorityManager
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function certify(Adherent $adherent, Administrator $administrator): void
    {
        $this->certifyAdherent($adherent, $administrator);

        $this->em->flush();
    }

    public function uncertify(Adherent $adherent, Administrator $administrator): void
    {
        $adherent->uncertify();

        $this->em->persist(AdherentCertificationHistory::createUncertify($adherent, $administrator));

        $this->em->flush();
    }

    public function approve(CertificationRequest $certificationRequest, Administrator $administrator): void
    {
        $certificationRequest->approve();
        $certificationRequest->process($administrator);

        $this->certifyAdherent($certificationRequest->getAdherent(), $administrator);

        $this->em->flush();
    }

    public function refuse(CertificationRequest $certificationRequest, Administrator $administrator): void
    {
        $certificationRequest->refuse();
        $certificationRequest->process($administrator);

        $this->em->flush();
    }

    public function block(CertificationRequest $certificationRequest, Administrator $administrator): void
    {
        $certificationRequest->block();
        $certificationRequest->process($administrator);

        $this->em->flush();
    }

    private function certifyAdherent(Adherent $adherent, Administrator $administrator): void
    {
        $adherent->certify();

        $this->em->persist(AdherentCertificationHistory::createCertify($adherent, $administrator));
    }
}
