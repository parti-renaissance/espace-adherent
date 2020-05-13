<?php

namespace App\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface EntityFileInterface
{
    public function getFile(): ?UploadedFile;

    public function setFile(?UploadedFile $file): void;

    public function setPath(?string $path): void;

    public function getPath(): ?string;

    public function getSlug(): ?string;

    public function getExtension(): ?string;

    public function setExtension(?string $extension): void;

    public function getPrefixPath(): string;
}
