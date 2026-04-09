<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class NationalEventInscriptionFields extends Constraint
{
    public string $messageNotBlank = 'Cette valeur ne doit pas être vide.';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
