<?php

namespace App\Normalizer\Procuration;

use App\Entity\ProcurationV2\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LimitedRequestDataNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'PROCURATION_LIMITED_REQUEST_NORMALIZER_ALREADY_CALLED';

    public function normalize($object, ?string $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $context['groups'] = ['procuration_proxy_list_request'];

        return $this->normalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return
            empty($context[self::ALREADY_CALLED])
            && $data instanceof Request
            && \in_array('procuration_proxy_list', $context['groups'] ?? [], true);
    }
}
