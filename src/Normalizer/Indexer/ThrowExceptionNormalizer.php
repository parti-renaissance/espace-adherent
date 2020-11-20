<?php

namespace App\Normalizer\Indexer;

use Algolia\SearchBundle\Searchable;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ThrowExceptionNormalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = [])
    {
        throw new \RuntimeException(sprintf('Normalizer not found for this indexable objet "%s"', \get_class($object)));
    }

    public function supportsNormalization($data, $format = null)
    {
        return Searchable::NORMALIZATION_FORMAT === $format;
    }
}
