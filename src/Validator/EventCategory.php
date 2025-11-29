<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class EventCategory extends Constraint
{
    public $errorPath = 'category';
    public $message = 'Catégorie est requise.';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
