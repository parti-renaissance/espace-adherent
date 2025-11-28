<?php

declare(strict_types=1);

namespace App\Normalizer\Procuration;

use ApiPlatform\State\Pagination\PaginatorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProxyNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function normalize($object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        foreach ($data['items'] as &$item) {
            $score = $item['score'];
            $item = $item[0];

            $item['matching_level'] = match ($score) {
                1 => 'department',
                2 => 'city',
                4 => 'borough',
                8 => 'vote_place',
                default => 'country',
            };
        }

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            PaginatorInterface::class => false,
        ];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return
            !isset($context[__CLASS__])
            && $data instanceof PaginatorInterface
            && \in_array('procuration_matched_proxy', $context['groups'] ?? [], true);
    }
}
