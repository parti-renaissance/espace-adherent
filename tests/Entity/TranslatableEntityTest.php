<?php

namespace Tests\App\Entity;

use App\Entity\EntityTranslationInterface;
use App\Entity\Timeline\Profile;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class TranslatableEntityTest extends TestCase
{
    public function testRemoveEmptyTranslations()
    {
        $translatable = new Profile();

        $translatable->setTranslations(new ArrayCollection([
            $french = $this->createTranslation('fr', false),
            $english = $this->createTranslation('en', true),
            $italian = $this->createTranslation('it', false),
            $german = $this->createTranslation('de', true),
        ]));

        $translatable->removeEmptyTranslations(['en', 'it']);

        $this->assertTrue($translatable->getTranslations()->contains($french));
        $this->assertFalse($translatable->getTranslations()->contains($english));
        $this->assertTrue($translatable->getTranslations()->contains($italian));
        $this->assertTrue($translatable->getTranslations()->contains($german));
    }

    public function testTranslate()
    {
        $translatable = new Profile();

        $translatable->setTranslations(new ArrayCollection([
            $french = $this->createTranslation('fr', false),
        ]));

        $this->assertSame($french, $translatable->translate('fr'));
        $this->assertNull($translatable->translate('en'));
    }

    private function createTranslation(string $locale, bool $empty = false): EntityTranslationInterface
    {
        $translation = $this->createMock(EntityTranslationInterface::class);

        $translation
            ->expects($this->any())
            ->method('getLocale')
            ->willReturn($locale)
        ;

        $translation
            ->expects($this->any())
            ->method('isEmpty')
            ->willReturn($empty)
        ;

        return $translation;
    }
}
