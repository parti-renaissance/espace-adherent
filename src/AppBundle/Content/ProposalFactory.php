<?php

namespace AppBundle\Content;

use AppBundle\Entity\Proposal;

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
        $proposal->setPublished($data['published'] ?? true);

        foreach ($data['themes'] as $theme) {
            $proposal->addTheme($theme);
        }

        return $proposal;
    }
}
