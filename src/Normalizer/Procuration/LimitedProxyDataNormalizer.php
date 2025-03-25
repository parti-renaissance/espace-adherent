<?php

namespace App\Normalizer\Procuration;

use App\Entity\ProcurationV2\Proxy;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LimitedProxyDataNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function normalize($object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $context['groups'] = ['procuration_request_list_proxy'];

        return $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Proxy::class => false,
        ];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return
            !isset($context[__CLASS__])
            && $data instanceof Proxy
            && \in_array('procuration_request_list', $context['groups'] ?? [], true);
    }
}
