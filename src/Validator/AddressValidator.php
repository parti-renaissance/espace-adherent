<?php

namespace App\Validator;

use App\Address\AddressInterface;
use App\FranceCities\FranceCities;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class AddressValidator extends ConstraintValidator
{
    private FranceCities $franceCities;

    public function __construct(FranceCities $franceCities)
    {
        $this->franceCities = $franceCities;
    }

    public function validate($address, Constraint $constraint)
    {
        dd('here');
        if (!$constraint instanceof Address) {
            throw new UnexpectedTypeException($constraint, Address::class);
        }

        if (null === $address || '' === $address) {
            return;
        }

        if (!$address instanceof AddressInterface) {
            throw new UnexpectedValueException($address, AddressInterface::class);
        }

        if ('fr' !== strtolower($address->getCountry())) {
            return;
        }

        if (!$address->getPostalCode() || !$address->getCity()) {
            return;
        }

        // Invalid postal code
        if (!is_scalar($address->getPostalCode()) || 0 === \count($this->franceCities->findCitiesByPostalCode($address->getPostalCode()))) {
            $this->context->addViolation($constraint->frenchPostalCodeMessage);

            return;
        }

        // Invalid city
        $parts = explode('-', $address->getCity());
        $city = $this->franceCities->getCityByInseeCode($parts[1]);
        if (2 !== \count($parts) || !\in_array($parts[0], $city ? $city->getPostalCode() : [], true)) {
            $this->context->addViolation($constraint->frenchCityMessage);

            return;
        }

        // City and zip code are not associated
        if ($parts[0] !== $address->getPostalCode()) {
            $this->context->addViolation($constraint->notAssociatedMessage);
        }
    }
}
