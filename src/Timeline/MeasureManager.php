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

    public function postPersist(Measure $measure): void
    {
        $this->index($measure);
    }

    public function postUpdate(Measure $measure): void
    {
        $this->index($measure);
    }

    public function postRemove(Measure $measure): void
    {
        $this->unIndex($measure);
    }

    private function index(Measure $measure): void
    {
        $this->algolia->index(array_merge([$measure], $measure->getThemesToIndex()->toArray()));
    }

    private function unIndex(Measure $measure): void
    {
        $this->algolia->index($measure->getThemesToIndex()->toArray());
        $this->algolia->unIndex($measure);
    }
}
