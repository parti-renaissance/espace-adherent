<?php

namespace App\Validator\Procuration;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ManualAssociations extends Constraint
{
    public string $message = 'procuration.request.manual_no_association';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
