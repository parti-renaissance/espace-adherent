<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ValidMessageFilterSegment extends Constraint
{
    public $message = 'Le segment n\'est pas autorisé';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
