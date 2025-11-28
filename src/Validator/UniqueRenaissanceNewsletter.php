<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueRenaissanceNewsletter extends Constraint
{
    public string $message = 'newsletter.already_registered';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
