<?php

namespace App\LocalElection;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class CandidateImportCommand
{
    #[Assert\NotBlank]
    #[Assert\File(maxSize: '5M', mimeTypes: ['text/plain', 'text/csv'])]
    public ?UploadedFile $file = null;
}
