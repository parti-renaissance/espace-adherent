<?php

namespace AppBundle\Content;

use AppBundle\Entity\OrderArticle;

class ExplainerFactory
{
    public function createFromArray(array $data): Explainer
    {
        $explainer = new Explainer();
        $explainer->setPosition($data['position']);
        $explainer->setTitle($data['title']);
        $explainer->setSlug($data['slug']);
        $explainer->setDescription($data['description']);
        $explainer->setMedia($data['media']);
        $explainer->setDisplayMedia($data['displayMedia'] ?? false);
        $explainer->setContent($data['content']);
        $explainer->setAmpContent($data['amp_content'] ?? '');
        $explainer->setPublished($data['published'] ?? true);
        $explainer->setKeywords($data['keywords'] ?? '');

        return $explainer;
    }
}
