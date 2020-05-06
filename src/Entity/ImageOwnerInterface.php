<?php

namespace App\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface ImageOwnerInterface
{
    public function setImageName(?UploadedFile $image): void;

    public function getImageName(): ?string;

    public function hasImageName(): bool;

    public function getImage(): ?UploadedFile;

    public function setImage(?UploadedFile $image): void;

    public function getImagePath(): string;
}
