<?php

namespace App\Normalizer\Procuration;

use App\Entity\ProcurationV2\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LimitedRequestDataNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function normalize($object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $context['groups'] = ['procuration_proxy_list_request'];

        return $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Request::class => false,
        ];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return
            !isset($context[__CLASS__])
            && $data instanceof Request
            && \in_array('procuration_proxy_list', $context['groups'] ?? [], true);
    }
}
