<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class TagAllInManagedAreaCodes extends Constraint
{
    public $message = 'common.managed_area.codes.invalid_message';
}
