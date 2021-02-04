<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ManagedZoneValidator extends AbstractInManagedZoneValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ManagedZone) {
            throw new UnexpectedTypeException($constraint, ManagedZone::class);
        }

        if (null === $value) {
            return;
        }

        $this->validateZones(
            $this->valueAsZones($value),
            $constraint
        );
    }
}
