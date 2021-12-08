<?php

namespace App\Validator;

use App\Intl\UnitedNationsBundle;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UnitedNationsCountryValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!UnitedNationsBundle::isCountryCode($value)) {
            $this->context->addViolation($constraint->message, ['{{ value }}' => $value]);
        }
    }
}
