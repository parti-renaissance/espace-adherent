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

    private const ALREADY_CALLED = 'JECOUTE_RIPOSTE_DENORMALIZER_ALREADY_CALLED';

    private RiposteOpenGraphHandler $riposteOpenGraphHandler;

    public function __construct(RiposteOpenGraphHandler $riposteOpenGraphHandler)
    {
        $this->riposteOpenGraphHandler = $riposteOpenGraphHandler;
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var Riposte $data */
        $data = $this->denormalizer->denormalize($data, $class, $format, $context);

        $this->riposteOpenGraphHandler->handle($data);

        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return is_a($type, Riposte::class, true);
    }
}
