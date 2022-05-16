<?php

namespace App\Normalizer;

use App\Address\AddressInterface;
use App\Entity\PostAddress;
use App\FranceCities\FranceCities;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AddressDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'ADDRESS_DENORMALIZER_ALREADY_CALLED';

    private FranceCities $franceCities;

    public function __construct(FranceCities $franceCities)
    {
        $this->franceCities = $franceCities;
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;
        $groups = $context['groups'] ?? [];

        if (
            !\array_key_exists('city', $data)
            && \array_key_exists('postal_code', $data)
            && \array_key_exists('city_name', $data)
        ) {
            $postalCode = $data['postal_code'];
            $cityName = $data['city_name'];

            $inseeCode = $this->franceCities->getCityInseeCode($postalCode ?? '', $cityName);

            if ($inseeCode) {
                $data['city'] = "$postalCode-$inseeCode";
            }
        }

        if (
            \in_array('contact_update', $groups, true)
            && \array_key_exists('postal_code', $data)
            && !\array_key_exists('country', $data)
        ) {
            $data['country'] = PostAddress::FRANCE;
        }

        return $this->denormalizer->denormalize($data, $class, $format, $context);
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return !isset($context[self::ALREADY_CALLED]) && is_a($type, AddressInterface::class, true);
    }
}
