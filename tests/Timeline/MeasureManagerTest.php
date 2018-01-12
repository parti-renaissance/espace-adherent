<?php

namespace Tests\AppBundle\Timeline;

use AppBundle\Algolia\ManualIndexerInterface;
use AppBundle\Entity\Timeline\Measure;
use AppBundle\Entity\Timeline\Theme;
use AppBundle\Timeline\MeasureManager;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class MeasureManagerTest extends TestCase
{
    public function testPostUpdate()
    {
        $measure = $this->createMock(Measure::class);
        $measure
            ->expects($this->once())
            ->method('getThemesToIndex')
            ->willReturn(new ArrayCollection([
                $theme1 = $this->createMock(Theme::class),
                $theme2 = $this->createMock(Theme::class),
            ]))
        ;

        $algoliaManualIndexer = $this->createMock(ManualIndexerInterface::class);
        $algoliaManualIndexer
            ->expects($this->once())
            ->method('index')
            ->with([$measure, $theme1, $theme2])
        ;

        $measureManager = new MeasureManager($algoliaManualIndexer);

        $measureManager->postUpdate($measure);
    }
}
