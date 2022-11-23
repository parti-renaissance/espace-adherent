<?php

namespace App\Validator;

use App\Address\Address;
use App\Donation\DonationRequest;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class FrenchAddressOrNationalityDonationValidator extends ConstraintValidator
{
    /**
     * @param DonationRequest                               $value
     * @param FrenchAddressOrNationalityDonation|Constraint $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof FrenchAddressOrNationalityDonation) {
            throw new UnexpectedTypeException($constraint, FrenchAddressOrNationalityDonation::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof DonationRequest) {
            throw new UnexpectedValueException($value, DonationRequest::class);
        }

        if (Address::FRANCE !== $value->getNationality() && Address::FRANCE !== $value->getAddress()->getCountry()) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
