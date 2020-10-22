<?php

namespace App\Adherent\Certification;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\CertificationRequest;
use App\Entity\Reporting\AdherentCertificationHistory;
use App\Membership\AdherentEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CertificationAuthorityManager
{
    private $em;
    private $documentManager;
    private $messageNotifier;
    private $eventDispatcher;

    public function __construct(
        EntityManagerInterface $em,
        CertificationRequestDocumentManager $documentManager,
        CertificationRequestNotifier $messageNotifier,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->em = $em;
        $this->documentManager = $documentManager;
        $this->messageNotifier = $messageNotifier;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function certify(Adherent $adherent, Administrator $administrator): void
    {
        $this->certifyAdherent($adherent, $administrator);
    }

    public function uncertify(Adherent $adherent, Administrator $administrator): void
    {
        $adherent->uncertify();

        $this->em->persist(AdherentCertificationHistory::createUncertify($adherent, $administrator));

        $this->em->flush();

        $this->eventDispatcher->dispatch(Events::ADHERENT_UNCERTIFIED, new AdherentEvent($adherent));
    }

    public function approve(
        CertificationRequest $certificationRequest,
        Administrator $administrator = null,
        bool $removeDocument = false
    ): void {
        $certificationRequest->approve();
        $certificationRequest->process($administrator);

        $this->certifyAdherent($certificationRequest->getAdherent(), $administrator);

        if ($removeDocument) {
            $this->removeDocument($certificationRequest);
        }

        $this->messageNotifier->sendApprovalMessage($certificationRequest);
    }

    public function refuse(CertificationRequestRefuseCommand $refuseCommand, bool $removeDocument = false): void
    {
        $certificationRequest = $refuseCommand->getCertificationRequest();

        $certificationRequest->refuse(
            $refuseCommand->getReason(),
            $refuseCommand->getCustomReason(),
            $refuseCommand->getComment()
        );
        $certificationRequest->process($refuseCommand->getAdministrator());

        if ($removeDocument) {
            $this->removeDocument($certificationRequest);
        }

        $this->em->flush();

        $this->messageNotifier->sendRefusalMessage($certificationRequest);
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

        $this->messageNotifier->sendBlockMessage($certificationRequest);
    }

    private function certifyAdherent(Adherent $adherent, Administrator $administrator = null): void
    {
        $adherent->certify();

        $this->em->persist(AdherentCertificationHistory::createCertify($adherent, $administrator));
        $this->em->flush();

        $this->eventDispatcher->dispatch(Events::ADHERENT_CERTIFIED, new AdherentEvent($adherent));
    }

    private function removeDocument(CertificationRequest $certificationRequest): void
    {
        $this->documentManager->removeDocument($certificationRequest);
    }
}
