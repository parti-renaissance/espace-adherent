<?php

namespace AppBundle\Validator;

use AppBundle\Donation\PayboxPaymentFrequency;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DonationFrequencyValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!PayboxPaymentFrequency::isValid($value)) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
