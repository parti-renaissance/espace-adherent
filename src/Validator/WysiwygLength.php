<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraints\Length;

#[\Attribute]
class WysiwygLength extends Length
{
}
