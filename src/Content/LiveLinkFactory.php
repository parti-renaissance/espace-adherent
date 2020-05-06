<?php

namespace App\Content;

use App\Entity\LiveLink;

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
