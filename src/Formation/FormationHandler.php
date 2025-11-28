<?php

declare(strict_types=1);

namespace App\Formation;

use App\Entity\AdherentFormation\Formation;
use League\Flysystem\FilesystemOperator;
use Ramsey\Uuid\Uuid;

class FormationHandler
{
    public function __construct(private readonly FilesystemOperator $defaultStorage)
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

        $formation->setFilePath($path = \sprintf(
            '%s/%s.%s',
            'files/adherent_formations',
            Uuid::uuid4()->toString(),
            $file->getClientOriginalExtension()
        ));

        $this->defaultStorage->write($path, file_get_contents($file->getPathname()));

        $formation->setFile(null);
    }

    private function removeFile(Formation $formation): void
    {
        if (!$filePath = $formation->getFilePath()) {
            return;
        }

        $formation->setFilePath(null);

        if (!$this->defaultStorage->has($filePath)) {
            return;
        }

        $this->defaultStorage->delete($filePath);
    }
}
