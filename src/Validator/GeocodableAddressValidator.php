<?php

declare(strict_types=1);

namespace App\Validator;

use App\Geocoder\Exception\GeocodingException;
use App\Geocoder\GeocodableInterface;
use App\Geocoder\Geocoder;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class GeocodableAddressValidator extends ConstraintValidator
{
    private $geocoder;

    public function __construct(Geocoder $geocoder)
    {
        $this->geocoder = $geocoder;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof GeocodableAddress) {
            throw new UnexpectedTypeException($constraint, GeocodableAddress::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof GeocodableInterface) {
            throw new UnexpectedValueException($value, GeocodableInterface::class);
        }

        if (!$this->isGeocodable($value->getGeocodableAddress())) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->setCode(GeocodableAddress::INVALID_ERROR)
                ->addViolation()
            ;
        }
    }

    private function isGeocodable(string $address): bool
    {
        $result = true;

        try {
            $this->geocoder->geocode($address);
        } catch (GeocodingException $exception) {
            $result = false;
        }

        return $result;
    }
}
