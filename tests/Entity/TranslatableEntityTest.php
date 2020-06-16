<?php

namespace Tests\App\Entity;

use App\Entity\Timeline\Profile;
use PHPUnit\Framework\TestCase;

class TranslatableEntityTest extends TestCase
{
    public function testRemoveEmptyTranslations()
    {
        $translatable = new Profile();

        $french = $translatable->translate('fr');
        $french->setTitle('titre');
        $english = $translatable->translate('en');
        $english->setTitle('title');
        $italian = $translatable->translate('it');
        $german = $translatable->translate('de');

        $translatable->mergeNewTranslations();

        $translations = $translatable->getTranslations();

        $this->assertContains($french, $translations);
        $this->assertContains($english, $translations);
        $this->assertNotContains($german, $translations);
        $this->assertNotContains($italian, $translations);
    }

    public function testTranslate()
    {
        $translatable = new Profile();

        $french = $translatable->translate('fr');
        $french->setTitle('titre');

        $this->assertSame($french, $translatable->translate('fr'));
        $this->assertNotContains($translatable->translate('en'), $translatable->getTranslations());
    }
}
