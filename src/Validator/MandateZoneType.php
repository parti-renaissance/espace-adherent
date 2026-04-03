<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class MandateZoneType extends Constraint
{
    public string $message = 'La zone sélectionnée n\'est pas valide pour ce type de mandat.';

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
