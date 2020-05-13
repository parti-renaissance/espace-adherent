<?php

namespace App\Admin\Extension;

use App\Admin\ImageUploadAdminInterface;
use App\Entity\Image;
use League\Flysystem\Filesystem;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;

class ImageUploadExtension extends AbstractAdminExtension
{
    private $storage;
    private $logger;

    public function __construct(Filesystem $storage, LoggerInterface $logger)
    {
        $this->storage = $storage;
        $this->logger = $logger;
    }

    public function prePersist(AdminInterface $admin, $object)
    {
        $this->saveImage($admin, $object);
    }

    public function preUpdate(AdminInterface $admin, $object)
    {
        $this->saveImage($admin, $object);
    }

    private function saveImage(ImageUploadAdminInterface $admin, $object): void
    {
        foreach ($admin->getUploadableImagePropertyNames() as $imagePropertyName) {
            $imagePropertyName = ucfirst($imagePropertyName);

            /** @var Image $image */
            if (null === $image = $object->{'get'.$imagePropertyName}()) {
                continue;
            }

            // If the image is optional (optional relation) and must be removed from file system and DB
            if ($image->isDeleted()) {
                $this->removeImageFile($image);
                $object->{'set'.$imagePropertyName}(null);
                continue;
            }

            // Continue if any file is uploaded
            if (!$image->getFile()) {
                continue;
            }

            // When the image already exists and new file is uploaded
            if ($image->getId()) {
                // Remove old image from storage
                $this->removeImageFile($image);

                // Create new Image entity
                $uploadedFile = $image->getFile();
                $image = new Image();
                $image->setFile($uploadedFile);
                $object->{'set'.$imagePropertyName}($image);
            }

            $image->syncWithUploadedFile();

            $path = $image->getFilePath();
            $this->storage->put($path, file_get_contents($image->getFile()->getPathname()));
        }
    }

    private function removeImageFile(Image $image): bool
    {
        try {
            return $this->storage->delete($image->getFilePath());
        } catch (\Exception $e) {
            $this->logger->warning(
                sprintf('Cannot delete image [%s], error: %s', $image->getFilePath(), $e->getMessage()),
                ['exception' => $e]
            );
        }

        return false;
    }

    public function postRemove(AdminInterface $admin, $object)
    {
        foreach ($admin->getUploadableImagePropertyNames($object) as $imagePropertyName) {
            $imagePropertyName = ucfirst($imagePropertyName);

            /** @var Image $image */
            if (null === $image = $object->{'get'.$imagePropertyName}()) {
                continue;
            }

            $this->removeImageFile($image);
        }
    }
}
