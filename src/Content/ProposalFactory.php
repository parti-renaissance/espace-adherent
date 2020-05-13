<?php

namespace App\Content;

use App\Entity\Proposal;

class ProposalFactory
{
    public function createFromArray(array $data): Proposal
    {
        $proposal = new Proposal();
        $proposal->setPosition($data['position']);
        $proposal->setTitle($data['title']);
        $proposal->setSlug($data['slug']);
        $proposal->setDescription($data['description']);
        $proposal->setMedia($data['media']);
        $proposal->setDisplayMedia($data['displayMedia'] ?? false);
        $proposal->setContent($data['content']);
        $proposal->setAmpContent($data['amp_content'] ?? '');
        $proposal->setPublished($data['published'] ?? true);
        $proposal->setKeywords($data['keywords'] ?? '');

        foreach ($data['themes'] as $theme) {
            $proposal->addTheme($theme);
        }

        return $proposal;
    }
}
