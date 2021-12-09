<?php

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

    public function validate($address, Constraint $constraint)
    {
        if (!$constraint instanceof GeocodableAddress) {
            throw new UnexpectedTypeException($constraint, GeocodableAddress::class);
        }

        if (null === $address || '' === $address) {
            return;
        }

        if (!$address instanceof GeocodableInterface) {
            throw new UnexpectedValueException($address, GeocodableInterface::class);
        }

        if (!$this->isGeocodable($address->getGeocodableAddress())) {
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
