<?php

declare(strict_types=1);

namespace App\Validator;

use App\Address\AddressInterface;
use App\Donation\Request\DonationRequest;
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

        if (AddressInterface::FRANCE !== $value->getNationality() && AddressInterface::FRANCE !== $value->getAddress()->getCountry()) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
