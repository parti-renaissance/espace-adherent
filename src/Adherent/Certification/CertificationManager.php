<?php

namespace App\Adherent\Certification;

use App\Entity\Adherent;
use App\Entity\CertificationRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CertificationManager
{
    private $entityManager;
    private $bus;
    private $documentManager;
    private $messageNotifier;

    public function __construct(
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus,
        CertificationRequestDocumentManager $documentManager,
        CertificationRequestNotifier $messageNotifier
    ) {
        $this->entityManager = $entityManager;
        $this->bus = $bus;
        $this->documentManager = $documentManager;
        $this->messageNotifier = $messageNotifier;
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

        $this->messageNotifier->sendPendingMessage($certificationRequest);
    }
}
