<?php

namespace App\Normalizer;

use App\Entity\Jecoute\Riposte;
use App\Riposte\RiposteOpenGraphHandler;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class JecouteRiposteDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function __construct(private readonly RiposteOpenGraphHandler $riposteOpenGraphHandler)
    {
    }

    public function denormalize($data, $class, $format = null, array $context = []): mixed
    {
        /** @var Riposte $data */
        $data = $this->denormalizer->denormalize($data, $class, $format, $context + [__CLASS__ => true]);

        $this->riposteOpenGraphHandler->handle($data);

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Riposte::class => false,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && is_a($type, Riposte::class, true);
    }
}
