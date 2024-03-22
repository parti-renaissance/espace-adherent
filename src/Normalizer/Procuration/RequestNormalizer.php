<?php

namespace App\Normalizer\Procuration;

use App\Entity\ProcurationV2\Request;
use App\Repository\Procuration\ProxyRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RequestNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'PROCURATION_REQUEST_NORMALIZER_ALREADY_CALLED';

    public function __construct(private readonly ProxyRepository $proxyRepository)
    {
    }

    public function normalize($object, ?string $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);
        $data['available_proxies_count'] = $this->proxyRepository->countAvailableProxies($object);

        return $data;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return
            empty($context[self::ALREADY_CALLED])
            && $data instanceof Request
            && \in_array('procuration_request_list', $context['groups'] ?? [], true);
    }
}
