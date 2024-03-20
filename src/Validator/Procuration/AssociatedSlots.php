<?php

namespace App\Validator\Procuration;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class AssociatedSlots extends Constraint
{
    public string $message = 'procuration.proxy.max_slots';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
