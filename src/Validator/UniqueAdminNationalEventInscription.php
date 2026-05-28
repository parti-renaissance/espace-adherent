<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class UniqueAdminNationalEventInscription extends Constraint
{
    public string $message = '<strong>Inscription(s) déjà existante(s)</strong> ({{ count }}) pour ce participant à cet événement :{{ table }}';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
