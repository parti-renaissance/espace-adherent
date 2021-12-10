<?php

namespace App\Normalizer;

use App\Address\Address;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PhoneNumberNormalizer implements NormalizerInterface, NormalizerAwareInterface, DenormalizerInterface, DenormalizerAwareInterface
{
    use NormalizerAwareTrait;
    use DenormalizerAwareTrait;

    private $util;

    public function __construct()
    {
        $this->util = PhoneNumberUtil::getInstance();
    }

    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'country' => $this->util->getRegionCodeForNumber($object),
            'number' => $this->util->format($object, PhoneNumberFormat::NATIONAL),
        ];
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        if (!\is_array($data) && !\is_string($data)) {
            return null;
        }

        if (\is_array($data)) {
            try {
                $phoneNumber = $this->util->parse($data['number'], $data['country']);
            } catch (NumberParseException $e) {
                // If the provided phone number can't be parsed,
                // then we manually create a value object that will fail at validation.
                $phoneNumber = new PhoneNumber();

                $phoneNumber->setCountryCode($data['country']);
                $phoneNumber->setNationalNumber($data['number']);
            }

            return $phoneNumber;
        }

        return $this->util->parse($data, Address::FRANCE);
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return $data instanceof PhoneNumber;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return PhoneNumber::class === $type;
    }
}
