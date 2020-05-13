<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class RepeatedValidator extends ConstraintValidator
{
    private static $value;

    public function validate($value, Constraint $constraint)
    {
        if (null === self::$value) {
            if (null === $value) {
                self::$value = '';
            } else {
                self::$value = $value;
            }

            return;
        }

        if (self::$value !== $value) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }

        self::$value = null;
    }
}
