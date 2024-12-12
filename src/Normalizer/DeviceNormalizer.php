<?php

namespace App\Normalizer;

use App\Entity\Device;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DeviceNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    protected const ALREADY_CALLED = 'DEVICE_NORMALIZER_ALREADY_CALLED';

    /**
     * @param Device $object
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $context[static::ALREADY_CALLED] = true;

        return $this->normalizer->normalize($object, $format, $context);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => null,
            Device::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[static::ALREADY_CALLED]) && $data instanceof Device;
    }
}
