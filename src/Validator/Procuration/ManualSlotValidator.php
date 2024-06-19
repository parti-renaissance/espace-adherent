<?php

namespace App\Validator\Procuration;

use App\Entity\ProcurationV2\AbstractSlot;
use App\Entity\ProcurationV2\ProxySlot;
use App\Entity\ProcurationV2\RequestSlot;
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

        if (!$value instanceof AbstractSlot) {
            throw new UnexpectedValueException($value, AbstractSlot::class);
        }

        if (!$value->manual) {
            return;
        }

        if (
            ($value instanceof RequestSlot && $value->proxySlot)
            || ($value instanceof ProxySlot && $value->requestSlot)
        ) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->atPath('manual')
                ->addViolation()
            ;
        }
    }
}
