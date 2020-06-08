<?php

namespace App\Adherent\Certification;

use App\Entity\CertificationRequest;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CertificationRequestDocumentManager
{
    private $storage;

    public function __construct(Filesystem $storage)
    {
        $this->storage = $storage;
    }

    public function uploadDocument(CertificationRequest $certificationRequest): void
    {
        if (!$certificationRequest->getDocument() instanceof UploadedFile) {
            throw new \RuntimeException(sprintf('The file must be an instance of %s', UploadedFile::class));
        }

        $certificationRequest->processDocument($certificationRequest->getDocument());
        $path = $certificationRequest->getPathWithDirectory();

        $this->storage->put($path, file_get_contents($certificationRequest->getDocument()->getPathname()));
    }

    public function removeDocument(CertificationRequest $certificationRequest): void
    {
        $filepath = $certificationRequest->getPathWithDirectory();

        if ($this->storage->has($filepath)) {
            $this->storage->delete($filepath);
        }

        $certificationRequest->removeDocument();
    }
}
