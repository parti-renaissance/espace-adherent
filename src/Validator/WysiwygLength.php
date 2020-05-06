<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraints\Length;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class WysiwygLength extends Length
{
}
