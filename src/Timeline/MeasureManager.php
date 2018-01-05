<?php

namespace AppBundle\Timeline;

use AppBundle\Algolia\ManualIndexerInterface;
use AppBundle\Entity\Timeline\Measure;
use AppBundle\Repository\Timeline\ThemeRepository;

class MeasureManager
{
    private $algolia;
    private $themeRepository;

    public function __construct(ManualIndexerInterface $algolia, ThemeRepository $themeRepository)
    {
        $this->algolia = $algolia;
        $this->themeRepository = $themeRepository;
    }

    public function preUpdate(Measure $measure): void
    {
        $themes = $this->themeRepository->findRelatedThemesForMeasure($measure);

        $this->algolia->index($themes);
    }
}
