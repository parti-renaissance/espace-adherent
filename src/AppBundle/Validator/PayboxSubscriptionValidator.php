<?php

namespace AppBundle\Validator;

use AppBundle\Donation\PayboxPaymentSubscription;
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
                ->addViolation();
        }
    }
}
