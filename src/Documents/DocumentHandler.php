<?php

namespace App\Documents;

use App\Entity\Document;
use League\Flysystem\FilesystemInterface;
use Ramsey\Uuid\Uuid;

class DocumentHandler
{
    public function __construct(private readonly FilesystemInterface $storage)
    {
    }

    public function handleFile(Document $document): void
    {
        if (!$file = $document->getFile()) {
            return;
        }

        $this->removeFile($document);

        $document->setFilePath($path = sprintf(
            '%s/%s.%s',
            'files/documents',
            Uuid::uuid4()->toString(),
            $file->getClientOriginalExtension()
        ));

        $this->storage->put($path, file_get_contents($file->getPathname()));

        $document->setFile(null);
    }

    private function removeFile(Document $document): void
    {
        if (!$filePath = $document->getFilePath()) {
            return;
        }

        $document->setFilePath(null);

        if (!$this->storage->has($filePath)) {
            return;
        }

        $this->storage->delete($filePath);
    }
}
