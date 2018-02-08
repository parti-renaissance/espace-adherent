<?php

namespace AppBundle\ImageGenerator;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;

abstract class AbstractImageGenerator implements ImageGeneratorInterface
{
    /**
     * @var ImageManager
     */
    protected $imageManager;

    public function __construct(ImageManager $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    public function getImageAsBase64(Image $image): string
    {
        return 'data:'.$image->mime().';base64,'.\base64_encode($image->getEncoded());
    }
}
