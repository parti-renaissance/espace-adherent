<?php

declare(strict_types=1);

namespace App\LocalElection;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class CandidateImportCommand
{
    #[Assert\File(maxSize: '5M', mimeTypes: ['text/plain', 'text/csv'])]
    #[Assert\NotBlank]
    public ?UploadedFile $file = null;
}
