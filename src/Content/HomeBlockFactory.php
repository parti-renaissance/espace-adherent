<?php

namespace App\Content;

use App\Entity\HomeBlock;

class HomeBlockFactory
{
    public function createFromArray(array $data): HomeBlock
    {
        $block = new HomeBlock();
        $block->setPosition($data['position']);
        $block->setPositionName($data['positionName']);
        $block->setTitle($data['title']);
        $block->setSubtitle($data['subtitle'] ?? null);
        $block->setLink($data['link']);
        $block->setType($data['type'] ?? 'article');
        $block->setMedia($data['media'] ?? null);
        $block->setTitleCta($data['titleCta'] ?? null);
        $block->setColorCta($data['colorCta'] ?? null);
        $block->setBgColor($data['bgColor'] ?? null);

        return $block;
    }
}
