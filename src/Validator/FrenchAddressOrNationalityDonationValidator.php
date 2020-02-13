<?php

namespace AppBundle\Validator;

use AppBundle\Donation\DonationRequest;
use AppBundle\Utils\AreaUtils;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

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
            throw new UnexpectedTypeException($value, DonationRequest::class);
        }

        if (AreaUtils::CODE_FRANCE !== $value->getNationality() && AreaUtils::CODE_FRANCE !== $value->getCountry()) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
