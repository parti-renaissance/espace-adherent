<?php

declare(strict_types=1);

namespace App\Image;

use App\Entity\ImageManageableInterface;

interface ImageManagerInterface
{
    public function saveImage(ImageManageableInterface $object): void;

    public function removeImage(ImageManageableInterface $object): void;
}
