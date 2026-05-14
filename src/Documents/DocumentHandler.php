<?php

declare(strict_types=1);

namespace App\Documents;

use App\Entity\Document;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Uid\Uuid;

class DocumentHandler
{
    public function __construct(private readonly FilesystemOperator $defaultStorage)
    {
    }

    public function handleFile(Document $document): void
    {
        if (!$file = $document->file) {
            return;
        }

        $this->removeFile($document);

        $document->filePath = \sprintf(
            '%s/%s.%s',
            'files/documents',
            Uuid::v4()->toRfc4122(),
            $file->getClientOriginalExtension()
        );

        $this->defaultStorage->write($document->filePath, file_get_contents($file->getPathname()));

        $document->file = null;
    }

    private function removeFile(Document $document): void
    {
        if (!$filePath = $document->filePath) {
            return;
        }

        $document->filePath = null;

        if (!$this->defaultStorage->has($filePath)) {
            return;
        }

        $this->defaultStorage->delete($filePath);
    }
}
