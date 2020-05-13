<?php

namespace App\Validator;

use App\Address\Address;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FrenchZipCodeValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$value) {
            return;
        }

        if (!$constraint instanceof FrenchZipCode) {
            throw new UnexpectedTypeException($constraint, FrenchZipCode::class);
        }

        /** @var Address $address */
        if (!($address = $this->context->getObject()) instanceof Address) {
            throw new UnexpectedTypeException($address, Address::class);
        }

        if (Address::FRANCE !== \strtoupper($address->getCountry())) {
            return;
        }

        if (!\is_numeric($value) || 5 !== \strlen($value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
