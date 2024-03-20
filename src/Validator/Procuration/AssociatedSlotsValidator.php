<?php

namespace App\Validator\Procuration;

use App\Entity\ProcurationV2\Proxy;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class AssociatedSlotsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof AssociatedSlots) {
            throw new UnexpectedTypeException($constraint, AssociatedSlots::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof Proxy) {
            throw new UnexpectedValueException($value, Proxy::class);
        }

        $maxSlots = $value->slots;

        if ($value->requests->count() > $maxSlots) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->atPath('requests')
                ->setParameter('{{ max_slots }}', $maxSlots)
                ->addViolation()
            ;
        }
    }
}
