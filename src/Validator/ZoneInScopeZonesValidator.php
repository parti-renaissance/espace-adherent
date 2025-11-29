<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Geo\Zone;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ZoneInScopeZonesValidator extends AbstractZonesInScopeZonesValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (null === $value) {
            return;
        }

        if (!$constraint instanceof ZoneInScopeZones) {
            throw new UnexpectedTypeException($constraint, ZoneInScopeZones::class);
        }

        if (!$value instanceof Zone) {
            throw new UnexpectedValueException($value, Zone::class);
        }

        $this->validateZones([$value], $constraint);
    }
}
