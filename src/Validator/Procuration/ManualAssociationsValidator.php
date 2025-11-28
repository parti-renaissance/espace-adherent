<?php

declare(strict_types=1);

namespace App\Validator\Procuration;

use App\Entity\ProcurationV2\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ManualAssociationsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ManualAssociations) {
            throw new UnexpectedTypeException($constraint, ManualAssociations::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof Request) {
            throw new UnexpectedValueException($value, Request::class);
        }

        if ($value->isManual() && $value->hasMatchedSlot()) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->atPath('status')
                ->addViolation()
            ;
        }
    }
}
