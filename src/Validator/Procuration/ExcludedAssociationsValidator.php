<?php

declare(strict_types=1);

namespace App\Validator\Procuration;

use App\Entity\Procuration\AbstractProcuration;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ExcludedAssociationsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ExcludedAssociations) {
            throw new UnexpectedTypeException($constraint, ExcludedAssociations::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof AbstractProcuration) {
            throw new UnexpectedValueException($value, AbstractProcuration::class);
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
