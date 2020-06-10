<?php

namespace App\Adherent\Certification;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\CertificationRequest;
use App\Entity\Reporting\AdherentCertificationHistory;
use App\Mailer\MailerService;
use App\Mailer\Message\CertificationRequestApprovedMessage;
use App\Mailer\Message\CertificationRequestBlockedMessage;
use App\Mailer\Message\CertificationRequestRefusedMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

class CertificationAuthorityManager
{
    private $em;
    private $mailer;
    private $urlGenerator;
    private $translator;
    private $documentManager;

    public function __construct(
        EntityManagerInterface $em,
        MailerService $transactionalMailer,
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface $translator,
        CertificationRequestDocumentManager $documentManager
    ) {
        $this->em = $em;
        $this->mailer = $transactionalMailer;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
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

        $this->mailer->sendMessage(CertificationRequestApprovedMessage::create($certificationRequest));
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

        $refusalReason = $certificationRequest->isRefusedWithOtherReason()
            ? $certificationRequest->getCustomRefusalReason()
            : $this->translator->trans($certificationRequest->getRefusalReason())
        ;

        $this->mailer->sendMessage(CertificationRequestRefusedMessage::create(
            $certificationRequest,
            $refusalReason,
            $this->urlGenerator->generate('app_certification_request_form')
        ));
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

        $this->mailer->sendMessage(CertificationRequestBlockedMessage::create($certificationRequest));
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
