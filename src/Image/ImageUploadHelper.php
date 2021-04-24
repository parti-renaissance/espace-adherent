<?php

namespace App\Image;

use App\Api\DTO\ImageContent;
use App\Entity\ImageOwnerInterface;
use Doctrine\ORM\EntityManagerInterface;

final class ImageUploadHelper
{
    private $entityManager;
    private $imageManager;

    public function __construct(EntityManagerInterface $entityManager, ImageManager $imageManager)
    {
        $this->entityManager = $entityManager;
        $this->imageManager = $imageManager;
    }

    public function uploadImage(ImageOwnerInterface $entity, ImageContent $imageContent): void
    {
        if ($entity->hasImageName()) {
            $this->imageManager->removeImage($entity);
        }

        $entity->setImage($imageContent->getFile());
        $this->imageManager->saveImage($entity);

        $this->entityManager->flush();
    }
}
