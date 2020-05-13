<?php

namespace App\Content;

use App\Entity\Media;
use Symfony\Component\HttpFoundation\File\File;

class MediaFactory
{
    public function createFromFile(string $name, string $path, File $file): Media
    {
        $media = new Media();
        $media->setName($name);
        $media->setPath($path);
        $media->setFile($file);

        return $media;
    }
}
