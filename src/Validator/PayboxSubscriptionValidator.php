<?php

namespace App\Validator;

use App\Donation\PayboxPaymentSubscription;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PayboxSubscriptionValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!PayboxPaymentSubscription::isValid($value)) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
