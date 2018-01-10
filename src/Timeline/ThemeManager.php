<?php

namespace AppBundle\Timeline;

use AppBundle\Algolia\ManualIndexerInterface;
use AppBundle\Entity\Timeline\Theme;

class ThemeManager
{
    private $algolia;

    public function __construct(ManualIndexerInterface $algolia)
    {
        $this->algolia = $algolia;
    }

    public function postPersist(Theme $theme): void
    {
        $this->index($theme);
    }

    public function postUpdate(Theme $theme): void
    {
        $this->index($theme);
    }

    public function postRemove(Theme $theme): void
    {
        $this->unIndex($theme);
    }

    private function index(Theme $theme): void
    {
        $this->algolia->index($theme);
    }

    private function unIndex(Theme $theme): void
    {
        $this->algolia->unIndex($theme);
    }
}
