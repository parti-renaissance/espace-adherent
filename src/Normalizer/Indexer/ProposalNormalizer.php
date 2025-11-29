<?php

declare(strict_types=1);

namespace App\Normalizer\Indexer;

use App\Entity\Proposal;

class ProposalNormalizer extends AbstractIndexerNormalizer
{
    /** @param Proposal $object */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        return [
            'title' => $object->getTitle(),
            'description' => $object->getDescription(),
            'keywords' => $object->getKeywords(),
            'slug' => $object->getSlug(),
            'created_at' => $this->formatDate($object->getCreatedAt()),
            'updated_at' => $this->formatDate($object->getCreatedAt()),
        ];
    }

    protected function getClassName(): string
    {
        return Proposal::class;
    }
}
