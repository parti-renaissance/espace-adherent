<?php

declare(strict_types=1);

namespace App\Normalizer\Procuration;

use App\Entity\ProcurationV2\Request;
use App\Repository\Procuration\ProxyRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RequestNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(private readonly ProxyRepository $proxyRepository)
    {
    }

    public function normalize($object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);
        $data['available_proxies_count'] = $this->proxyRepository->countAvailableProxies($object);

        return $data;
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
            && \in_array('procuration_request_list', $context['groups'] ?? [], true);
    }
}
