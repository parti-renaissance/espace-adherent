<?php

declare(strict_types=1);

namespace App\Validator\Procuration;

use App\Entity\Procuration\AbstractSlot;
use App\Entity\Procuration\ProxySlot;
use App\Entity\Procuration\RequestSlot;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ManualSlotValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ManualSlot) {
            throw new UnexpectedTypeException($constraint, ManualSlot::class);
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
