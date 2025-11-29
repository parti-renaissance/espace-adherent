<?php

declare(strict_types=1);

namespace App\Validator;

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

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof FrenchAddress) {
            throw new UnexpectedTypeException($constraint, FrenchAddress::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof AddressInterface) {
            throw new UnexpectedValueException($value, AddressInterface::class);
        }

        if (AddressInterface::FRANCE !== $value->getCountry() || !$value->getPostalCode()) {
            return;
        }

        if (
            !\is_scalar($value->getPostalCode())
            || 0 === \count($this->franceCities->findCitiesByPostalCode($value->getPostalCode()))
        ) {
            $this
                ->context
                ->buildViolation($constraint->invalidFrenchPostalCodeMessage)
                ->atPath('postalCode')
                ->addViolation()
            ;

            return;
        }

        if ($value->getCityName()) {
            if (!$this->franceCities->getCityByPostalCodeAndName($value->getPostalCode(), $value->getCityName())) {
                $this
                    ->context
                    ->buildViolation($constraint->postalCodeAndCityNameNotMatchingMessage)
                    ->atPath('postalCode')
                    ->addViolation()
                ;
            }
        }

        if ($value->getCity()) {
            // Invalid city
            $parts = explode('-', $value->getCity());
            $city = $this->franceCities->getCityByInseeCode($parts[1]);
            if (2 !== \count($parts) || !\in_array($parts[0], $city ? $city->getPostalCode() : [], true)) {
                $this->context->addViolation($constraint->invalidCityMessage);

                return;
            }

            // City and zip code are not associated
            if ($parts[0] !== $value->getPostalCode()) {
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
