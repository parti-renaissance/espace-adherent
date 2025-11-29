<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Geocoder\Exception\GeocodingException;
use App\Geocoder\Geocoder;
use App\Geocoder\GeoPointInterface;
use Symfony\Component\Intl\Exception\MissingResourceException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class PostAddressDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function __construct(private readonly Geocoder $geocoder)
    {
    }

    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        /** @var GeoPointInterface $entity */
        $entity = $this->denormalizer->denormalize($data, $type, $format, $context + [__CLASS__ => true]);

        try {
            if ($entity->getGeocodableHash() !== md5($address = $entity->getGeocodableAddress())) {
                $entity->updateCoordinates($this->geocoder->geocode($address));
            }
        } catch (GeocodingException|MissingResourceException $e) {
            // do nothing when an exception arises
        }

        return $entity;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            GeoPointInterface::class => false,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && is_a($type, GeoPointInterface::class, true);
    }
}
