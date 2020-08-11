<?php

namespace App\Image;

use App\Entity\ImageOwnerInterface;

interface ImageManagerInterface
{
    public function saveImage(ImageOwnerInterface $object): void;

    public function removeImage(ImageOwnerInterface $object): void;
}
