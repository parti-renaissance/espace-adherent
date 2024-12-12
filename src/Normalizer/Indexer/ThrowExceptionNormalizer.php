<?php

namespace App\Normalizer\Indexer;

use Algolia\SearchBundle\Searchable;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ThrowExceptionNormalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = [])
    {
        throw new \RuntimeException(\sprintf('Normalizer not found for this indexable objet "%s"', $object::class));
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => false,
        ];
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return Searchable::NORMALIZATION_FORMAT === $format;
    }
}
