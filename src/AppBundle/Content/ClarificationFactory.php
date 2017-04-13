<?php

namespace AppBundle\Content;

use AppBundle\Entity\Clarification;

class ClarificationFactory
{
    public function createFromArray(array $data): Clarification
    {
        $clarification = new Clarification();
        $clarification->setTitle($data['title']);
        $clarification->setSlug($data['slug']);
        $clarification->setDescription($data['description']);
        $clarification->setMedia($data['media']);
        $clarification->setDisplayMedia($data['displayMedia'] ?? false);
        $clarification->setContent($data['content']);
        $clarification->setPublished($data['published'] ?? true);
        $clarification->setKeywords($data['keywords'] ?? '');

        return $clarification;
    }
}
