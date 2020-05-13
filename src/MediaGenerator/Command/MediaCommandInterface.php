<?php

namespace App\MediaGenerator\Command;

use Symfony\Component\HttpFoundation\File\File;

interface MediaCommandInterface
{
    public function getBackgroundImage(): ?File;

    public function getImagePath(): string;

    public function getBackgroundColor(): ?string;
}
