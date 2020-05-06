<?php

namespace App\Content;

use App\Entity\Clarification;

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
