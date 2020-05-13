<?php

namespace App\Content;

use App\Entity\OrderArticle;

class OrderArticleFactory
{
    public function createFromArray(array $data): OrderArticle
    {
        $article = new OrderArticle();
        $article->setPosition($data['position']);
        $article->setTitle($data['title']);
        $article->setSlug($data['slug']);
        $article->setDescription($data['description']);
        $article->setMedia($data['media']);
        $article->setDisplayMedia($data['displayMedia'] ?? false);
        $article->setContent($data['content']);
        $article->setAmpContent($data['amp_content'] ?? '');
        $article->setPublished($data['published'] ?? true);
        $article->setKeywords($data['keywords'] ?? '');

        foreach ($data['sections'] as $section) {
            $article->addSection($section);
        }

        return $article;
    }
}
