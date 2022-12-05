<?php

namespace App\Validator\Jecoute;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ReferentNews extends Constraint
{
    public string $invalidZoneType = 'Cette zone ne correspond pas à une région, un département ou un arrondissement';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
