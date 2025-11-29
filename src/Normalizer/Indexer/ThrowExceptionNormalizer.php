<?php

declare(strict_types=1);

namespace App\Normalizer\Indexer;

use Algolia\SearchBundle\Searchable;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ThrowExceptionNormalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        throw new \RuntimeException(\sprintf('Normalizer not found for this indexable objet "%s"', $object::class));
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return Searchable::NORMALIZATION_FORMAT === $format;
    }
}
