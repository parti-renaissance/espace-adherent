<?php

namespace App\Adherent\Certification;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\CertificationRequest;
use App\Entity\Reporting\AdherentCertificationHistory;
use Doctrine\ORM\EntityManagerInterface;

class CertificationAuthorityManager
{
    private $em;
    private $documentManager;

    public function __construct(EntityManagerInterface $em, CertificationRequestDocumentManager $documentManager)
    {
        $this->em = $em;
        $this->documentManager = $documentManager;
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

        $this->removeDocument($certificationRequest);

        $this->em->flush();
    }

    public function refuse(CertificationRequestRefuseCommand $refuseCommand): void
    {
        $certificationRequest = $refuseCommand->getCertificationRequest();

        $certificationRequest->refuse(
            $refuseCommand->getReason(),
            $refuseCommand->getCustomReason(),
            $refuseCommand->getComment()
        );
        $certificationRequest->process($refuseCommand->getAdministrator());

        $this->removeDocument($certificationRequest);

        $this->em->flush();
    }

    public function block(CertificationRequestBlockCommand $blockCommand): void
    {
        $certificationRequest = $blockCommand->getCertificationRequest();

        $certificationRequest->block(
            $blockCommand->getReason(),
            $blockCommand->getCustomReason(),
            $blockCommand->getComment()
        );
        $certificationRequest->process($blockCommand->getAdministrator());

        $this->removeDocument($certificationRequest);

        $this->em->flush();
    }

    private function certifyAdherent(Adherent $adherent, Administrator $administrator): void
    {
        $adherent->certify();

        $this->em->persist(AdherentCertificationHistory::createCertify($adherent, $administrator));
    }

    private function removeDocument(CertificationRequest $certificationRequest): void
    {
        $this->documentManager->removeDocument($certificationRequest);
    }
}
