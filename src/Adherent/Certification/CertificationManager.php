<?php

namespace App\Adherent\Certification;

use App\Entity\Adherent;
use App\Entity\CertificationRequest;
use App\Mailer\MailerService;
use App\Mailer\Message\CertificationRequestPendingMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CertificationManager
{
    private $entityManager;
    private $bus;
    private $mailer;
    private $documentManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus,
        MailerService $transactionalMailer,
        CertificationRequestDocumentManager $documentManager
    ) {
        $this->entityManager = $entityManager;
        $this->bus = $bus;
        $this->mailer = $transactionalMailer;
        $this->documentManager = $documentManager;
    }

    public function createRequest(Adherent $adherent): CertificationRequest
    {
        return $adherent->startCertificationRequest();
    }

    public function handleRequest(CertificationRequest $certificationRequest): void
    {
        $this->documentManager->uploadDocument($certificationRequest);

        $this->entityManager->flush();

        $this->bus->dispatch(new CertificationRequestProcessCommand($certificationRequest->getUuid()));

        $this->mailer->sendMessage(CertificationRequestPendingMessage::create($certificationRequest));
    }
}
