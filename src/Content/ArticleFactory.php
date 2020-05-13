<?php

namespace App\Content;

use App\Entity\Article;

class ArticleFactory
{
    public function createFromArray(array $data): Article
    {
        $article = new Article();
        $article->setTitle($data['title']);
        $article->setSlug($data['slug']);
        $article->setDescription($data['description']);
        $article->setMedia($data['media']);
        $article->setDisplayMedia($data['displayMedia'] ?? false);
        $article->setContent($data['content']);
        $article->setAmpContent($data['amp_content'] ?? '');
        $article->setPublished($data['published'] ?? true);
        $article->setPublishedAt($data['publishedAt'] ?? new \DateTime());
        $article->setCategory($data['category']);
        $article->setKeywords($data['keywords'] ?? '');

        return $article;
    }
}
