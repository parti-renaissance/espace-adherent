<?php

namespace App\Validator;

use App\Committee\CommitteeEvent;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\DefaultEvent;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class EventCategoryValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof EventCategory) {
            throw new UnexpectedTypeException($constraint, EventCategory::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof BaseEvent) {
            throw new UnexpectedValueException($value, BaseEvent::class);
        }

        if (null === $value->getCategory()
            && \in_array(\get_class($value), [DefaultEvent::class, CommitteeEvent::class], true)) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->atPath($constraint->errorPath)
                ->addViolation()
            ;
        }
    }
}
