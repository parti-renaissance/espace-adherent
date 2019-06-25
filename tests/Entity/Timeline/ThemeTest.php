<?php

namespace Tests\AppBundle\Entity\Timeline;

use AppBundle\Entity\Media;
use AppBundle\Entity\Timeline\Measure;
use AppBundle\Entity\Timeline\MeasureTranslation;
use AppBundle\Entity\Timeline\Profile;
use AppBundle\Entity\Timeline\Theme;
use AppBundle\Entity\Timeline\ThemeTranslation;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class ThemeTest extends TestCase
{
    public function testMeasureTitles()
    {
        $theme = new Theme();

        $theme->addMeasure($this->createMeasure(null, 'Titre 1', 'Title 1'));
        $theme->addMeasure($this->createMeasure(null, 'Titre 2', 'Title 2'));
        // Check that Theme::measureTitles does not return duplicates
        $theme->addMeasure($this->createMeasure(null, 'Title 1', 'Titre 1'));

        $this->assertEquals(['Titre 1', 'Title 1', 'Titre 2', 'Title 2'], $theme->getMeasureTitles());
    }

    public function testMeasureIds()
    {
        $theme = new Theme();

        $theme->addMeasure($this->createMeasure(6));
        $theme->addMeasure($this->createMeasure(44));
        $theme->addMeasure($this->createMeasure(59));

        $this->assertEquals([6, 44, 59], $theme->getMeasureIds());
    }

    public function testProfileIds()
    {
        $theme = new Theme();

        $profile1 = $this->createProfile(1);
        $profile2 = $this->createProfile(2);
        $profile3 = $this->createProfile(3);

        $theme->addMeasure($this->createMeasure(null, null, null, [$profile1, $profile2]));
        $theme->addMeasure($this->createMeasure(null, null, null, [$profile2, $profile3]));

        $this->assertEquals([1, 2, 3], $theme->getProfileIds());
    }

    public function testImage()
    {
        $theme = new Theme();

        $this->assertNull($theme->getImage());

        $media = $this->createMock(Media::class);
        $media
            ->expects($this->once())
            ->method('getPathWithDirectory')
            ->willReturn('images/timeline/theme-agriculture.jpg')
        ;

        $theme->setMedia($media);

        $this->assertEquals('images/timeline/theme-agriculture.jpg', $theme->getImage());
    }

    public function testTitles()
    {
        $theme = new Theme();

        // No translation
        $this->assertEmpty($theme->getTitles());

        // French only
        $theme->addTranslation($this->createThemeTranslation('fr', 'Titre'));

        $this->assertEquals(['fr' => 'Titre', 'en' => 'Titre'], $theme->getTitles());

        // French + English
        $theme->addTranslation($this->createThemeTranslation('en', 'Title'));

        $this->assertEquals(['fr' => 'Titre', 'en' => 'Title'], $theme->getTitles());
    }

    public function testSlugs()
    {
        $theme = new Theme();

        // No translation
        $this->assertEmpty($theme->getSlugs());

        // French only
        $theme->addTranslation($this->createThemeTranslation('fr', null, 'titre'));

        $this->assertEquals(['fr' => 'titre', 'en' => 'titre'], $theme->getSlugs());

        // French + English
        $theme->addTranslation($this->createThemeTranslation('en', null, 'title'));

        $this->assertEquals(['fr' => 'titre', 'en' => 'title'], $theme->getSlugs());
    }

    public function testDescriptions()
    {
        $theme = new Theme();

        // No translation
        $this->assertEmpty($theme->getDescriptions());

        // French only
        $theme->addTranslation($this->createThemeTranslation('fr', null, null, 'Courte description'));

        $this->assertEquals([
            'fr' => 'Courte description',
            'en' => 'Courte description',
        ], $theme->getDescriptions());

        // French + English
        $theme->addTranslation($this->createThemeTranslation('en', null, null, 'Short description'));

        $this->assertEquals([
            'fr' => 'Courte description',
            'en' => 'Short description',
        ], $theme->getDescriptions());
    }

    private function createMeasure(
        int $id = null,
        string $frenchTitle = null,
        string $englishTitle = null,
        array $profiles = []
    ): Measure {
        $measure = $this->createMock(Measure::class);

        if ($id) {
            $measure
                ->expects($this->any())
                ->method('getId')
                ->willReturn($id)
            ;
        }

        $translations = [];

        if ($frenchTitle) {
            $measure
                ->expects($this->any())
                ->method('translate')
                ->with('fr')
                ->willReturn($frenchTranslation = $this->createMeasureTranslation('fr', $frenchTitle))
            ;

            $translations[] = $frenchTranslation;
        }

        if ($englishTitle) {
            $measure
                ->expects($this->any())
                ->method('translate')
                ->with('en')
                ->willReturn($englishTranslation = $this->createMeasureTranslation('en', $englishTitle))
            ;

            $translations[] = $englishTranslation;
        }

        if ($translations) {
            $measure
                ->expects($this->any())
                ->method('getTranslations')
                ->willReturn(new ArrayCollection($translations))
            ;
        }

        if ($profiles) {
            $measure
                ->expects($this->any())
                ->method('getProfiles')
                ->willReturn(new ArrayCollection($profiles))
            ;
        }

        return $measure;
    }

    private function createMeasureTranslation(string $locale, string $title): MeasureTranslation
    {
        $translation = $this->createMock(MeasureTranslation::class);

        $translation
            ->expects($this->any())
            ->method('getLocale')
            ->willReturn($locale)
        ;

        $translation
            ->expects($this->any())
            ->method('getTitle')
            ->willReturn($title)
        ;

        return $translation;
    }

    private function createProfile(int $id): Profile
    {
        $profile = $this->createMock(Profile::class);

        $profile
            ->expects($this->any())
            ->method('getId')
            ->willReturn($id)
        ;

        return $profile;
    }

    private function createThemeTranslation(
        string $locale,
        string $title = null,
        string $slug = null,
        string $description = null
    ): ThemeTranslation {
        $translation = $this->createMock(ThemeTranslation::class);

        $translation
            ->expects($this->any())
            ->method('getLocale')
            ->willReturn($locale)
        ;

        if ($title) {
            $translation
                ->expects($this->any())
                ->method('getTitle')
                ->willReturn($title)
            ;
        }

        if ($slug) {
            $translation
                ->expects($this->any())
                ->method('getSlug')
                ->willReturn($slug)
            ;
        }

        if ($description) {
            $translation
                ->expects($this->any())
                ->method('getDescription')
                ->willReturn($description)
            ;
        }

        return $translation;
    }
}
