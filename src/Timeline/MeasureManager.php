<?php

namespace AppBundle\Timeline;

use AppBundle\Algolia\ManualIndexerInterface;
use AppBundle\Entity\Timeline\Measure;

class MeasureManager
{
    private $algolia;

    public function __construct(ManualIndexerInterface $algolia)
    {
        $this->algolia = $algolia;
    }

    public function postUpdate(Measure $measure): void
    {
        $this->algolia->index(array_merge([$measure], $measure->getThemesToIndex()->toArray()));
    }
}
