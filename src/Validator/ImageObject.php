<?php

namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraints\Image;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class ImageObject extends Image
{
}
