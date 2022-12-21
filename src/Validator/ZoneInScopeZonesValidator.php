<?php

namespace App\Validator;

use App\Entity\Geo\Zone;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ZoneInScopeZonesValidator extends ConstraintValidator
{
    public function __construct(private readonly ScopeGeneratorResolver $scopeGeneratorResolver)
    {
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ZoneInScopeZones) {
            throw new UnexpectedTypeException($constraint, ZoneInScopeZones::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof Zone) {
            throw new UnexpectedValueException($value, Zone::class);
        }

        $scope = $this->scopeGeneratorResolver->generate();

        if (!\in_array($value, $scope?->getZones())) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
