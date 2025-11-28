<?php

declare(strict_types=1);

namespace App\Validator\Procuration;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ExcludedAssociations extends Constraint
{
    public string $message = 'procuration.proxy.excluded_no_association';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
