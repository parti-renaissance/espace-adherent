<?php

namespace App\Normalizer;

use App\Entity\PostAddress;
use App\Geocoder\Exception\GeocodingException;
use App\Geocoder\Geocoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class PostAddressDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'GEOPOINT_DENORMALIZER_ALREADY_CALLED';

    private Geocoder $geocoder;

    public function __construct(Geocoder $geocoder)
    {
        $this->geocoder = $geocoder;
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;
        $entity = $this->denormalizer->denormalize($data, $type, $format, $context);

        if ($entity->getGeocodableHash() !== md5($address = $entity->getGeocodableAddress())) {
            try {
                $entity->updateCoordinates($this->geocoder->geocode($address));
            } catch (GeocodingException $e) {
                // do nothing when an exception arises
            }
        }

        return $entity;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return is_a($type, PostAddress::class, true) && !isset($context[self::ALREADY_CALLED]);
    }
}
