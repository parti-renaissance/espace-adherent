<?php

namespace App\Validator;

use App\Entity\Event\Event;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class EventCategoryValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof EventCategory) {
            throw new UnexpectedTypeException($constraint, EventCategory::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof Event) {
            throw new UnexpectedValueException($value, Event::class);
        }

        if (null === $value->getCategory()) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->atPath($constraint->errorPath)
                ->addViolation()
            ;
        }
    }
}
