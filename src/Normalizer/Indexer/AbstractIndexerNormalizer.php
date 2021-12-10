<?php

namespace App\Normalizer\Indexer;

use Algolia\SearchBundle\Searchable;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class AbstractIndexerNormalizer implements NormalizerInterface
{
    final public function supportsNormalization($data, $format = null)
    {
        return is_a($data, $this->getClassName()) && Searchable::NORMALIZATION_FORMAT === $format;
    }

    abstract protected function getClassName(): string;

    protected function formatDate(?\DateTimeInterface $dateTime, string $format = 'd/m/Y H:i'): ?string
    {
        return $dateTime ? $dateTime->format($format) : null;
    }
}
