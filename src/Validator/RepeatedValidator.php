<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RepeatedValidator extends ConstraintValidator
{
    private static $value;

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Repeated) {
            throw new UnexpectedTypeException($constraint, Repeated::class);
        }

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
