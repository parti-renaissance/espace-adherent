<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Timeline\Measure;
use AppBundle\Entity\Timeline\Theme;
use PHPUnit\Framework\TestCase;

class MeasureTest extends TestCase
{
    public function testGetThemesToIndex()
    {
        $measure = new Measure();

        $theme1 = $this->createMock(Theme::class);
        $theme2 = $this->createMock(Theme::class);

        $measure->addTheme($theme1);
        $measure->saveCurrentThemes();

        $measure->removeTheme($theme1);
        $measure->addTheme($theme2);

        $currentThemes = $measure->getThemes();
        $this->assertCount(1, $currentThemes);
        $this->assertFalse($currentThemes->contains($theme1));
        $this->assertTrue($currentThemes->contains($theme2));

        $themesToIndex = $measure->getThemesToIndex();
        $this->assertCount(2, $themesToIndex);
        $this->assertContainsOnlyInstancesOf(Theme::class, $themesToIndex);
        $this->assertTrue($themesToIndex->contains($theme1));
        $this->assertTrue($themesToIndex->contains($theme2));
    }
}
