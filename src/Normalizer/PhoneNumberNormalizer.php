<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Address\AddressInterface;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PhoneNumberNormalizer implements NormalizerInterface, DenormalizerInterface
{
    private $util;

    public function __construct()
    {
        $this->util = PhoneNumberUtil::getInstance();
    }

    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        if (array_intersect(['profile_read', 'phoning_campaign_call_read'], $context['groups'] ?? [])) {
            return [
                'country' => $this->util->getRegionCodeForNumber($object),
                'number' => $this->util->format($object, PhoneNumberFormat::NATIONAL),
            ];
        }

        return $this->util->format($object, PhoneNumberFormat::INTERNATIONAL);
    }

    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        if (!\is_array($data) && !\is_string($data)) {
            return null;
        }

        if (\is_array($data)) {
            if (empty($data['number']) || empty($data['country'])) {
                return null;
            }

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
        } elseif (\strlen($data) < 2 || \strlen($data) > 17) {
            $phoneNumber = new PhoneNumber();
            $phoneNumber->setCountryCode(33);
            $phoneNumber->setNationalNumber($data);

            return $phoneNumber;
        }

        try {
            return $this->util->parse($data, AddressInterface::FRANCE);
        } catch (NumberParseException $e) {
            return null;
        }
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            PhoneNumber::class => true,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof PhoneNumber;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return PhoneNumber::class === $type;
    }
}
