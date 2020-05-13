<?php

namespace App\Validator;

use App\Address\AddressInterface;
use App\Intl\FranceCitiesBundle;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @Annotation
 */
class AddressValidator extends ConstraintValidator
{
    public function validate($address, Constraint $constraint)
    {
        if (!$constraint instanceof Address) {
            throw new UnexpectedTypeException($constraint, Address::class);
        }

        if (null === $address || '' === $address) {
            return;
        }

        if (!$address instanceof AddressInterface) {
            throw new UnexpectedTypeException($address, AddressInterface::class);
        }

        if ('fr' !== strtolower($address->getCountry())) {
            return;
        }

        if (!$address->getPostalCode() || !$address->getCity()) {
            return;
        }

        // Invalid postal code
        if (!is_scalar($address->getPostalCode()) || 0 === \count(FranceCitiesBundle::getPostalCodeCities($address->getPostalCode()))) {
            $this->context->addViolation($constraint->frenchPostalCodeMessage);

            return;
        }

        // Invalid city
        $parts = explode('-', $address->getCity());
        if (2 !== \count($parts) || !FranceCitiesBundle::getCity($parts[0], $parts[1])) {
            $this->context->addViolation($constraint->frenchCityMessage);

            return;
        }

        // City and zip code are not associated
        if ($parts[0] !== $address->getPostalCode()) {
            $this->context->addViolation($constraint->notAssociatedMessage);
        }
    }
}
