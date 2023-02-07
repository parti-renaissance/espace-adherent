<?php

namespace App\Formation;

use App\Entity\AdherentFormation\Formation;
use League\Flysystem\FilesystemInterface;
use Ramsey\Uuid\Uuid;

class FormationHandler
{
    public function __construct(private readonly FilesystemInterface $storage)
    {
    }

    public function handleFile(Formation $formation): void
    {
        $file = $formation->getFile();

        if (!$formation->isFileContent() || $file) {
            $this->removeFile($formation);
        }

        if (!$file) {
            return;
        }

        $formation->setFilePath($path = sprintf(
            '%s/%s.%s',
            'files/adherent_formations',
            Uuid::uuid4()->toString(),
            $file->getClientOriginalExtension()
        ));
        $formation->setFileExtension($file->getExtension());

        $this->storage->put($path, file_get_contents($file->getPathname()));

        $formation->setFile(null);
    }

    private function removeFile(Formation $formation): void
    {
        if (!$filePath = $formation->getFilePath()) {
            return;
        }

        $formation->setFilePath(null);
        $formation->setFileExtension(null);

        if (!$this->storage->has($filePath)) {
            return;
        }

        $this->storage->delete($filePath);
    }
}
