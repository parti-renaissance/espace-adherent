<?php

namespace App\Normalizer\Indexer;

use App\Entity\Clarification;

class ClarificationNormalizer extends AbstractIndexerNormalizer
{
    /** @param Clarification $object */
    public function normalize($object, $format = null, array $context = [])
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
        return Clarification::class;
    }
}
