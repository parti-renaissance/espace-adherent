<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TagAllInManagedAreaCodesValidator extends ConstraintValidator
{
    private const ALL = 'ALL';

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof TagAllInManagedAreaCodes) {
            throw new UnexpectedTypeException($constraint, TagAllInManagedAreaCodes::class);
        }

        if (null === $value) {
            return;
        }

        if (\is_array($value)
            && \count($value) > 1
            && \in_array(self::ALL, $value, true)
        ) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
