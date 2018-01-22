<?php

namespace Tests\AppBundle\Entity\Timeline;

use AppBundle\Entity\Timeline\Profile;
use AppBundle\Entity\Timeline\ProfileTranslation;
use PHPUnit\Framework\TestCase;

class ProfileTest extends TestCase
{
    public function testTitles()
    {
        $profile = new Profile();

        // No translation
        $this->assertEmpty($profile->titles());

        // French only
        $profile->addTranslation($this->createTranslation('fr', 'Titre'));

        $this->assertEquals(['fr' => 'Titre', 'en' => 'Titre'], $profile->titles());

        // French + English
        $profile->addTranslation($this->createTranslation('en', 'Title'));

        $this->assertEquals(['fr' => 'Titre', 'en' => 'Title'], $profile->titles());
    }

    public function testSlugs()
    {
        $profile = new Profile();

        // No translation
        $this->assertEmpty($profile->slugs());

        // French only
        $profile->addTranslation($this->createTranslation('fr', null, 'titre'));

        $this->assertEquals(['fr' => 'titre', 'en' => 'titre'], $profile->slugs());

        // French + English
        $profile->addTranslation($this->createTranslation('en', null, 'title'));

        $this->assertEquals(['fr' => 'titre', 'en' => 'title'], $profile->slugs());
    }

    public function testDescriptions()
    {
        $profile = new Profile();

        // No translation
        $this->assertEmpty($profile->descriptions());

        // French only
        $profile->addTranslation($this->createTranslation('fr', null, null, 'Courte description'));

        $this->assertEquals([
            'fr' => 'Courte description',
            'en' => 'Courte description',
        ], $profile->descriptions());

        // French + English
        $profile->addTranslation($this->createTranslation('en', null, null, 'Short description'));

        $this->assertEquals([
            'fr' => 'Courte description',
            'en' => 'Short description',
        ], $profile->descriptions());
    }

    private function createTranslation(
        string $locale,
        string $title = null,
        string $slug = null,
        string $description = null
    ): ProfileTranslation {
        $translation = $this->createMock(ProfileTranslation::class);

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
