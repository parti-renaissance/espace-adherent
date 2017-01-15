<?php

namespace AppBundle\Content;

use AppBundle\Entity\Article;

class ArticleFactory
{
    public function createFromArray(array $data): Article
    {
        $article = new Article();
        $article->setTitle($data['title']);
        $article->setSlug($data['slug']);
        $article->setDescription($data['description']);
        $article->setMedia($data['media']);
        $article->setContent($data['content']);

        return $article;
    }
}
