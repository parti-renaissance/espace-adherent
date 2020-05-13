<?php

namespace App\Normalizer;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class UuidNormalizer implements DenormalizerInterface
{
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return Uuid::fromString($data);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return UuidInterface::class === $type && \is_string($data);
    }
}
