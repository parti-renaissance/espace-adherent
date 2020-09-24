<?php

namespace App\Normalizer\Indexer;

use App\Entity\CustomSearchResult;

class CustomSearchResultNormalizer extends AbstractIndexerNormalizer
{
    /** @param CustomSearchResult $object */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'id' => $object->getId(),
            'title' => $object->getTitle(),
            'description' => $object->getDescription(),
            'keywords' => $object->getKeywords(),
            'url' => $object->getUrl(),
        ];
    }

    protected function getClassName(): string
    {
        return CustomSearchResult::class;
    }
}
