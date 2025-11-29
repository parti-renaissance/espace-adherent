<?php

namespace App\Validator;

use App\Donation\Request\DonationRequest;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class MaxMonthDonationValidator extends ConstraintValidator
{
    /**
     * @param DonationRequest             $value
     * @param MaxMonthDonation|Constraint $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof MaxMonthDonation) {
            throw new UnexpectedTypeException($constraint, MaxMonthDonation::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof DonationRequest) {
            throw new UnexpectedValueException($value, DonationRequest::class);
        }

        if ($value->isSubscription() && $value->getAmount() * 100 > $constraint->maxDonationInCents) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ max_amount }}', (string) ($constraint->maxDonationInCents / 100))
                ->addViolation()
            ;
        }
    }
}
