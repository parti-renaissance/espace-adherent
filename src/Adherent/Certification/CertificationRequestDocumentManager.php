<?php

declare(strict_types=1);

namespace App\Adherent\Certification;

use App\Entity\CertificationRequest;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CertificationRequestDocumentManager
{
    private $storage;

    public function __construct(FilesystemOperator $defaultStorage)
    {
        $this->storage = $defaultStorage;
    }

    public function uploadDocument(CertificationRequest $certificationRequest): void
    {
        if (!$certificationRequest->getDocument() instanceof UploadedFile) {
            throw new \RuntimeException(\sprintf('The file must be an instance of %s', UploadedFile::class));
        }

        $certificationRequest->processDocument($certificationRequest->getDocument());
        $path = $certificationRequest->getPathWithDirectory();

        $this->storage->write($path, file_get_contents($certificationRequest->getDocument()->getPathname()));
    }

    public function removeDocument(CertificationRequest $certificationRequest): void
    {
        if (!$certificationRequest->hasDocument()) {
            return;
        }

        $filepath = $certificationRequest->getPathWithDirectory();

        if ($this->storage->has($filepath)) {
            $this->storage->delete($filepath);
        }

        $certificationRequest->removeDocument();
    }
}
