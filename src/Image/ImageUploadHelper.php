<?php

declare(strict_types=1);

namespace App\Image;

use App\Api\DTO\ImageContent;
use App\Entity\ImageManageableInterface;
use App\Image\Event\ImageUploadedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class ImageUploadHelper
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ImageManager $imageManager,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function uploadImage(ImageManageableInterface $entity, ImageContent $imageContent): void
    {
        if ($entity->hasImageName()) {
            $this->imageManager->removeImage($entity);
        }

        $entity->setImage($imageContent->getFile());
        $this->imageManager->saveImage($entity);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new ImageUploadedEvent($entity));
    }

    public function removeImage(ImageManageableInterface $entity): void
    {
        if (!$entity->hasImageName()) {
            return;
        }

        $this->imageManager->removeImage($entity);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new ImageUploadedEvent($entity));
    }
}
