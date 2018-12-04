<?php

namespace AppBundle\IdeasWorkshop;

use AppBundle\Entity\IdeasWorkshop\Theme;
use AppBundle\Storage\ImageStorage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ThemeManager
{
    public function __construct(ImageStorage $imageStorage)
    {
        $this->imageStorage = $imageStorage;
    }

    public function saveImage(Theme $theme): void
    {
        if (!$theme->getImage() instanceof UploadedFile) {
            throw new \RuntimeException(sprintf('The image must be an instance of %s', UploadedFile::class));
        }

        $oldPath = $theme->hasImageName() ? $theme->getImagePath() : null;

        $theme->setImageName($theme->getImage());

        $this->imageStorage->save($theme->getImage(), $theme->getImagePath(), $oldPath);
    }

    public function removeImage(Theme $theme): void
    {
        if (!$theme->hasImageName()) {
            throw new \RuntimeException('This theme does not contain an image.');
        }

        $this->imageStorage->remove($theme->getImagePath());

        $theme->setImageName(null);
    }
}
