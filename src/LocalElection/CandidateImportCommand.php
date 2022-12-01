<?php

namespace App\LocalElection;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class CandidateImportCommand
{
    /**
     * @Assert\File(
     *     maxSize="5M",
     *     mimeTypes={
     *         "text/plain",
     *         "text/csv",
     *     }
     * )
     */
    private ?UploadedFile $file;

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): void
    {
        $this->file = $file;
    }
}
