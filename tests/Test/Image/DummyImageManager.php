<?php

declare(strict_types=1);

namespace Tests\App\Test\Image;

use App\Entity\ImageManageableInterface;
use App\Image\ImageManagerInterface;

class DummyImageManager implements ImageManagerInterface
{
    public function saveImage(ImageManageableInterface $object): void
    {
        $object->setImageName($object->getImage());
    }

    public function removeImage(ImageManageableInterface $object): void
    {
        $object->setImageName(null);
    }
}
