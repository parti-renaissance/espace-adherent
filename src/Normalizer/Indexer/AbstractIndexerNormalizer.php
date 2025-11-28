<?php

declare(strict_types=1);

namespace App\Normalizer\Indexer;

use Algolia\SearchBundle\Searchable;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class AbstractIndexerNormalizer implements NormalizerInterface
{
    final public function getSupportedTypes(?string $format): array
    {
        return [
            $this->getClassName() => true,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return is_a($data, $this->getClassName()) && Searchable::NORMALIZATION_FORMAT === $format;
    }

    abstract protected function getClassName(): string;

    protected function formatDate(?\DateTimeInterface $dateTime, string $format = 'd/m/Y H:i'): ?string
    {
        return $dateTime?->format($format);
    }
}
