<?php

declare(strict_types=1);

namespace App\Admin\Extension;

use App\Entity\ImageManageableInterface;
use App\Image\ImageManagerInterface;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;

class SimpleImageUploadExtension extends AbstractAdminExtension
{
    private ImageManagerInterface $imageManager;

    public function __construct(ImageManagerInterface $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    public function prePersist(AdminInterface $admin, object $object): void
    {
        $this->saveImage($object);
    }

    public function preUpdate(AdminInterface $admin, object $object): void
    {
        $this->saveImage($object);
    }

    public function postRemove(AdminInterface $admin, object $object): void
    {
        if (!$object instanceof ImageManageableInterface) {
            return;
        }

        $this->imageManager->removeImage($object);
    }

    private function saveImage($object): void
    {
        if (!$object instanceof ImageManageableInterface) {
            return;
        }

        if ($object->getImage()) {
            $this->imageManager->saveImage($object);
        }
    }
}
