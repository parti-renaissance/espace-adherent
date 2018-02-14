<?php

namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DeleteValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param string $value      The value that should be validated
     * @param Delete $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value !== $constraint->sameText) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
