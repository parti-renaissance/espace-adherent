<?php

namespace App\Normalizer;

use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer as BaseDateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Workaround for https://github.com/symfony/symfony/issues/27824
 */
class DateTimeNormalizer implements DenormalizerInterface, NormalizerInterface
{
    private BaseDateTimeNormalizer $decorated;

    public function __construct(BaseDateTimeNormalizer $decorated)
    {
        $this->decorated = $decorated;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        return $this->decorated->normalize($object, $format, $context);
    }

    public function getSupportedTypes(?string $format): array
    {
        return $this->decorated->getSupportedTypes($format);
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        try {
            return $this->decorated->denormalize($data, $class, $format, $context);
        } catch (NotNormalizableValueException $e) {
            return null;
        }
    }

    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return $this->decorated->supportsDenormalization($data, $type, $format);
    }
}
