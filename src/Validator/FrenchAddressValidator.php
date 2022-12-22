<?php

namespace App\Validator;

use App\Address\Address as AddressObject;
use App\Address\AddressInterface;
use App\FranceCities\FranceCities;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class FrenchAddressValidator extends ConstraintValidator
{
    private FranceCities $franceCities;

    public function __construct(FranceCities $franceCities)
    {
        $this->franceCities = $franceCities;
    }

    public function validate($address, Constraint $constraint)
    {
        if (!$constraint instanceof FrenchAddress) {
            throw new UnexpectedTypeException($constraint, FrenchAddress::class);
        }

        if (null === $address) {
            return;
        }

        if (!$address instanceof AddressInterface) {
            throw new UnexpectedValueException($address, AddressInterface::class);
        }

        if (AddressObject::FRANCE !== $address->getCountry() || !$address->getPostalCode()) {
            return;
        }

        if (
            !\is_scalar($address->getPostalCode())
            || 0 === \count($this->franceCities->findCitiesByPostalCode($address->getPostalCode()))
        ) {
            $this
                ->context
                ->buildViolation($constraint->invalidFrenchPostalCodeMessage)
                ->atPath('postalCode')
                ->addViolation()
            ;

            return;
        }

        if ($address->getCityName()) {
            if (!$this->franceCities->getCityByPostalCodeAndName($address->getPostalCode(), $address->getCityName())) {
                $this
                    ->context
                    ->buildViolation($constraint->postalCodeAndCityNameNotMatchingMessage)
                    ->atPath('postalCode')
                    ->addViolation()
                ;
            }
        }

        if ($address->getCity()) {
            // Invalid city
            $parts = explode('-', $address->getCity());
            $city = $this->franceCities->getCityByInseeCode($parts[1]);
            if (2 !== \count($parts) || !\in_array($parts[0], $city ? $city->getPostalCode() : [], true)) {
                $this->context->addViolation($constraint->invalidCityMessage);

                return;
            }

            // City and zip code are not associated
            if ($parts[0] !== $address->getPostalCode()) {
                $this
                    ->context
                    ->buildViolation($constraint->postalCodeAndCityNotMatchingMessage)
                    ->atPath('postalCode')
                    ->addViolation()
                ;
            }
        }
    }
}
