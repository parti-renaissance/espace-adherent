<?php

namespace App\Validator\Procuration;

use App\Entity\ProcurationV2\Proxy;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ExcludedAssociationsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ExcludedAssociations) {
            throw new UnexpectedTypeException($constraint, ExcludedAssociations::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof Proxy) {
            throw new UnexpectedValueException($value, Proxy::class);
        }

        if ($value->isExcluded() && ($value->hasMatchedSlot() || $value->hasManualSlot())) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->atPath('status')
                ->addViolation()
            ;
        }
    }
}
