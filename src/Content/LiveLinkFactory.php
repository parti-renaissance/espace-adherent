<?php

namespace AppBundle\Content;

use AppBundle\Entity\LiveLink;

class LiveLinkFactory
{
    public function createFromArray(array $data): LiveLink
    {
        $block = new LiveLink();
        $block->setPosition($data['position']);
        $block->setTitle($data['title']);
        $block->setLink($data['link']);

        return $block;
    }
}
