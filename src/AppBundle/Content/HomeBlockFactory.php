<?php

namespace AppBundle\Content;

use AppBundle\Entity\HomeBlock;

class HomeBlockFactory
{
    public function createFromArray(array $data): HomeBlock
    {
        $block = new HomeBlock();
        $block->setPosition($data['position']);
        $block->setPositionName($data['positionName']);
        $block->setTitle($data['title']);
        $block->setSubtitle($data['subtitle']);
        $block->setLink($data['link']);
        $block->setType($data['type'] ?? 'article');
        $block->setMedia($data['media'] ?? null);

        return $block;
    }
}
