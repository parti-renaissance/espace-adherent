<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Address\AddressInterface;
use App\FranceCities\FranceCities;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AddressDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function __construct(private readonly FranceCities $franceCities)
    {
    }

    public function denormalize($data, $class, $format = null, array $context = []): mixed
    {
        $groups = $context['groups'] ?? [];

        if (
            !\array_key_exists('city', $data)
            && !empty($data['postal_code'])
            && !empty($data['city_name'])
        ) {
            $postalCode = $data['postal_code'];
            $cityName = $data['city_name'];

            $city = $this->franceCities->getCityByPostalCodeAndName($postalCode, $cityName);

            if ($city) {
                $data['city'] = \sprintf('%s-%s', $postalCode, $city->getInseeCode());
                $data['city_name'] = $city->getName();
            }
        }

        if (
            \in_array('contact_update', $groups, true)
            && \array_key_exists('postal_code', $data)
            && !\array_key_exists('country', $data)
        ) {
            $data['country'] = AddressInterface::FRANCE;
        }

        return $this->denormalizer->denormalize($data, $class, $format, $context + [__CLASS__ => true]);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            AddressInterface::class => false,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && is_a($type, AddressInterface::class, true);
    }
}
