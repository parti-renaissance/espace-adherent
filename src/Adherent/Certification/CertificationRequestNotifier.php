<?php

namespace App\Adherent\Certification;

use App\Entity\CertificationRequest;
use App\Mailer\MailerService;
use App\Mailer\Message\CertificationRequestApprovedMessage;
use App\Mailer\Message\CertificationRequestBlockedMessage;
use App\Mailer\Message\CertificationRequestPendingMessage;
use App\Mailer\Message\CertificationRequestRefusedMessage;
use App\Mailer\Message\Renaissance\Certification\RenaissanceCertificationRequestApprovedMessage;
use App\Mailer\Message\Renaissance\Certification\RenaissanceCertificationRequestBlockedMessage;
use App\Mailer\Message\Renaissance\Certification\RenaissanceCertificationRequestPendingMessage;
use App\Mailer\Message\Renaissance\Certification\RenaissanceCertificationRequestRefusedMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CertificationRequestNotifier
{
    private MailerService $mailer;
    private UrlGeneratorInterface $urlGenerator;
    private TranslatorInterface $translator;
    private string $renaissanceHost;

    public function __construct(
        MailerService $transactionalMailer,
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface $translator,
        string $renaissanceHost
    ) {
        $this->mailer = $transactionalMailer;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
        $this->renaissanceHost = $renaissanceHost;
    }

    public function sendPendingMessage(CertificationRequest $certificationRequest): void
    {
        $this->mailer->sendMessage(
            $certificationRequest->getAdherent()->isRenaissanceUser()
                ? RenaissanceCertificationRequestPendingMessage::create($certificationRequest)
                : CertificationRequestPendingMessage::create($certificationRequest)
        );
    }

    public function sendApprovalMessage(CertificationRequest $certificationRequest): void
    {
        $this->mailer->sendMessage(
            $certificationRequest->getAdherent()->isRenaissanceUser()
                ? RenaissanceCertificationRequestApprovedMessage::create($certificationRequest)
                : CertificationRequestApprovedMessage::create($certificationRequest)
        );
    }

    public function sendRefusalMessage(CertificationRequest $certificationRequest): void
    {
        $refusalReason = $certificationRequest->isRefusedWithOtherReason()
            ? $certificationRequest->getCustomRefusalReason()
            : $this->translator->trans($certificationRequest->getRefusalReasonKey());

        $certificationRequestUrl = $this->urlGenerator->generate(
            'app_certification_request_form',
            $certificationRequest->getAdherent()->isRenaissanceUser()
                ? ['app_domain' => $this->renaissanceHost]
                : [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $this->mailer->sendMessage(
            $certificationRequest->getAdherent()->isRenaissanceUser()
                ? RenaissanceCertificationRequestRefusedMessage::create($certificationRequest, $refusalReason, $certificationRequestUrl)
                : CertificationRequestRefusedMessage::create($certificationRequest, $refusalReason, $certificationRequestUrl)
        );
    }

    public function sendBlockMessage(CertificationRequest $certificationRequest): void
    {
        $this->mailer->sendMessage(
            $certificationRequest->getAdherent()->isRenaissanceUser()
                ? RenaissanceCertificationRequestBlockedMessage::create($certificationRequest)
                : CertificationRequestBlockedMessage::create($certificationRequest)
        );
    }
}
