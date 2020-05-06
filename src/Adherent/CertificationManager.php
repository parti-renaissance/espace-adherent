<?php

namespace App\Adherent;

use App\Entity\Adherent;
use App\Entity\CertificationRequest;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CertificationManager
{
    private $entityManager;
    private $storage;

    public function __construct(EntityManagerInterface $entityManager, Filesystem $storage)
    {
        $this->entityManager = $entityManager;
        $this->storage = $storage;
    }

    public function createRequest(Adherent $adherent): CertificationRequest
    {
        return $adherent->startCertificationRequest();
    }

    public function handleRequest(CertificationRequest $certificationRequest): void
    {
        $this->uploadDocument($certificationRequest);

        $this->entityManager->flush();
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
