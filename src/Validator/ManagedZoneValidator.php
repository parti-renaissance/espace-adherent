<?php

namespace App\Validator;

use App\Entity\ZoneableEntity;
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

        if ($value instanceof ZoneableEntity) {
            $managedZones = $value->getZones()->toArray();

            if (null === $value = $value->{$constraint->zoneGetMethodName}()) {
                return;
            }
        }

        $this->validateZones(
            $this->valueAsZones($value),
            $constraint,
            $managedZones ?? null
        );
    }
}
