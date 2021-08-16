<?php

namespace App\Validator;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ValidMessageFilterSegmentValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        if (!$constraint instanceof ValidMessageFilterSegment) {
            throw new UnexpectedTypeException($constraint, ValidMessageFilterSegment::class);
        }

        if (!$value instanceof AdherentMessageFilterInterface) {
            throw new UnexpectedValueException($value, AdherentMessageFilterInterface::class);
        }

        if (!($segment = $value->getSegment()) || !($filter = $segment->getFilter())) {
            return;
        }

        if ($value->getMessage()->getAuthor() !== $segment->getAuthor()
            || !$value->getMessage()->isCompatibleWithScope($filter->getScope())) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath('segment')
                ->addViolation()
            ;
        }
    }
}
