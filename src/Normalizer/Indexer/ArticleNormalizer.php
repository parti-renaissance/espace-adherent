<?php

namespace App\Normalizer\Indexer;

use App\Entity\Article;

class ArticleNormalizer extends AbstractIndexerNormalizer
{
    /** @param Article $object */
    public function normalize($object, $format = null, array $context = [])
    {
        $category = $object->getCategory();

        return [
            'title' => $object->getTitle(),
            'description' => $object->getDescription(),
            'slug' => $object->getSlug(),
            'keywords' => $object->getKeywords(),
            'created_at' => $this->formatDate($object->getCreatedAt()),
            'updated_at' => $this->formatDate($object->getUpdatedAt()),
            'category' => [
                'name' => $category ? $category->getName() : '',
                'slug' => $category ? $category->getSlug() : '',
            ],
        ];
    }

    protected function getClassName(): string
    {
        return Article::class;
    }
}
