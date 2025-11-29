<?php

declare(strict_types=1);

namespace App\Image;

use App\Entity\ImageManageableInterface;
use App\Storage\ImageStorage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageManager implements ImageManagerInterface
{
    /**
     * @var ImageStorage
     */
    private $imageStorage;

    public function __construct(ImageStorage $imageStorage)
    {
        $this->imageStorage = $imageStorage;
    }

    public function saveImage(ImageManageableInterface $object): void
    {
        if (!$object->getImage() instanceof UploadedFile) {
            throw new \RuntimeException(\sprintf('The image must be an instance of %s', UploadedFile::class));
        }

        $oldPath = $object->hasImageName() ? $object->getImagePath() : null;

        $object->setImageName($object->getImage());

        $this->imageStorage->save($object->getImage(), $object->getImagePath(), $oldPath);
    }

    public function removeImage(ImageManageableInterface $object): void
    {
        if (!$object->hasImageName()) {
            throw new \RuntimeException('This object does not contain an image.');
        }

        if ($this->imageStorage->has($object->getImagePath())) {
            $this->imageStorage->remove($object->getImagePath());
        }

        $object->setImageName(null);
    }
}
