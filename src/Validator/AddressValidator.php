<?php

declare(strict_types=1);

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

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Address) {
            throw new UnexpectedTypeException($constraint, Address::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof AddressInterface) {
            throw new UnexpectedValueException($value, AddressInterface::class);
        }

        if ('fr' !== strtolower((string) $value->getCountry())) {
            return;
        }

        if (!$value->getPostalCode() || !$value->getCity()) {
            return;
        }

        // Invalid postal code
        if (!\is_scalar($value->getPostalCode()) || 0 === \count($this->franceCities->findCitiesByPostalCode($value->getPostalCode()))) {
            $this->context->addViolation($constraint->frenchPostalCodeMessage);

            return;
        }

        // Invalid city
        $parts = explode('-', $value->getCity());
        $city = $this->franceCities->getCityByInseeCode($parts[1]);
        if (2 !== \count($parts) || !\in_array($parts[0], $city ? $city->getPostalCode() : [], true)) {
            $this->context->addViolation($constraint->frenchCityMessage);

            return;
        }

        // City and zip code are not associated
        if ($parts[0] !== $value->getPostalCode()) {
            $this->context->addViolation($constraint->notAssociatedMessage);
        }
    }
}
