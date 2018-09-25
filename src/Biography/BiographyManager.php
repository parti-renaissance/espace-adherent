<?php

namespace AppBundle\Biography;

use AppBundle\Entity\Biography\AbstractBiography;
use AppBundle\Storage\ImageStorage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BiographyManager
{
    /**
     * @var ImageStorage
     */
    private $imageStorage;

    public function __construct(ImageStorage $imageStorage)
    {
        $this->imageStorage = $imageStorage;
    }

    /**
     * Uploads and saves the biography image.
     */
    public function saveImage(AbstractBiography $biography): void
    {
        if (!$biography->getImage() instanceof UploadedFile) {
            throw new \RuntimeException(sprintf('The image must be an instance of %s', UploadedFile::class));
        }

        $oldPath = $biography->hasImageName() ? $biography->getImagePath() : null;

        $biography->setImageName($biography->getImage());

        $this->imageStorage->save($biography->getImage(), $biography->getImagePath(), $oldPath);
    }

    /**
     *  Removes the biography image.
     */
    public function removeImage(AbstractBiography $biography): void
    {
        if (!$biography->hasImageName()) {
            throw new \RuntimeException('This biography does not contain an image.');
        }

        $this->imageStorage->remove($biography->getImagePath());

        $biography->setImageName(null);
    }
}
