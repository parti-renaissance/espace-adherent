<?php

namespace App\Normalizer\Procuration;

use ApiPlatform\State\Pagination\PaginatorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProxyNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'PROCURATION_PROXY_NORMALIZER_ALREADY_CALLED';

    public function normalize($object, ?string $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        foreach ($data['items'] as &$item) {
            $score = $item['score'];
            $item = $item[0];

            $item['matching_level'] = match ($score) {
                1 => 'city',
                2 => 'vote_place',
                default => 'department/country',
            };
        }

        return $data;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return
            empty($context[self::ALREADY_CALLED])
            && $data instanceof PaginatorInterface
            && \in_array('procuration_proxy_read', $context['groups'] ?? [], true);
    }
}
