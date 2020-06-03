<?php

namespace App\Adherent;

use App\Entity\Adherent;
use App\Entity\CertificationRequest;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;

class CertificationManager
{
    private $entityManager;
    private $storage;
    private $bus;

    public function __construct(EntityManagerInterface $entityManager, Filesystem $storage, MessageBusInterface $bus)
    {
        $this->entityManager = $entityManager;
        $this->storage = $storage;
        $this->bus = $bus;
    }

    public function createRequest(Adherent $adherent): CertificationRequest
    {
        return $adherent->startCertificationRequest();
    }

    public function handleRequest(CertificationRequest $certificationRequest): void
    {
        $this->uploadDocument($certificationRequest);

        $this->entityManager->flush();

        $this->bus->dispatch(new CertificationRequestProcessCommand($certificationRequest->getUuid()));
    }

    private function uploadDocument(CertificationRequest $certificationRequest): void
    {
        if (!$certificationRequest->getDocument() instanceof UploadedFile) {
            throw new \RuntimeException(sprintf('The file must be an instance of %s', UploadedFile::class));
        }

        $certificationRequest->processDocument($certificationRequest->getDocument());
        $path = $certificationRequest->getPathWithDirectory();

        $this->storage->put($path, file_get_contents($certificationRequest->getDocument()->getPathname()));
    }
}
