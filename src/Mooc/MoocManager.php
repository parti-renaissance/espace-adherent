<?php

namespace AppBundle\Mooc;

use AppBundle\Entity\Mooc\Mooc;
use AppBundle\Storage\ImageStorage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MoocManager
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
     * Uploads and saves the mooc image.
     */
    public function saveImage(Mooc $mooc): void
    {
        if (!$mooc->getImage() instanceof UploadedFile) {
            throw new \RuntimeException(sprintf('The image must be an instance of %s', UploadedFile::class));
        }

        $oldPath = $mooc->hasImageName() ? $mooc->getImagePath() : null;

        $mooc->setImageName($mooc->getImage());

        $this->imageStorage->save($mooc->getImage(), $mooc->getImagePath(), $oldPath);
    }

    /**
     *  Removes the mooc image.
     */
    public function removeImage(Mooc $mooc): void
    {
        if (!$mooc->hasImageName()) {
            throw new \RuntimeException('This mooc does not contain an image.');
        }

        $this->imageStorage->remove($mooc->getImagePath());

        $mooc->setImageName(null);
    }
}
