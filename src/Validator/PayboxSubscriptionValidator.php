<?php

namespace App\Validator;

use App\Donation\PayboxPaymentSubscription;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PayboxSubscriptionValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PayboxSubscription) {
            throw new UnexpectedTypeException($constraint, PayboxSubscription::class);
        }

        if (!PayboxPaymentSubscription::isValid($value)) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
