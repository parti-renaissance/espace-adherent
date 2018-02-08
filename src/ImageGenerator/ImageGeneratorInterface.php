<?php

namespace AppBundle\ImageGenerator;

use AppBundle\ImageGenerator\Command\ImageCommandInterface;
use Intervention\Image\Image;

interface ImageGeneratorInterface
{
    /**
     * @param ImageCommandInterface $command
     * @return Image
     */
    public function generate(ImageCommandInterface $command): Image;

    /**
     * @param Image $image
     * @return string
     */
    public function getImageAsBase64(Image $image): string;
}
