<?php

namespace App\Normalizer;

use App\Address\Address;
use App\Intl\FranceCitiesBundle;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AddressDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'ADDRESS_DENORMALIZER_ALREADY_CALLED';

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        if (
            !\array_key_exists('city', $data)
            && \array_key_exists('postal_code', $data)
            && \array_key_exists('city_name', $data)
        ) {
            $postalCode = $data['postal_code'];
            $cityName = $data['city_name'];

            $inseeCode = FranceCitiesBundle::getCityInseeCode($postalCode, $cityName);

            if ($inseeCode) {
                $data['city'] = "$postalCode-$inseeCode";
            }
        }

        /** @var Address $data */
        $data = $this->denormalizer->denormalize($data, $class, $format, $context);

        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return !isset($context[self::ALREADY_CALLED]) && Address::class === $type;
    }
}
