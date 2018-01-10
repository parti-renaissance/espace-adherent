<?php

namespace AppBundle\Entity;

use A2lix\I18nDoctrineBundle\Doctrine\ORM\Util\Translatable;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

trait EntityTranslatableTrait
{
    use Translatable;

    /**
     * @var EntityTranslationInterface[]|Collection
     *
     * @Assert\Valid
     */
    private $translations;

    public function translate(string $locale = null): ?EntityTranslationInterface
    {
        if (!$locale) {
            $locale = $this->getDefaultLocale();
        }

        $translation = $this->translations->filter(
            function (EntityTranslationInterface $translation) use ($locale) {
                return $locale === $translation->getLocale();
            }
        )->first();

        return $translation ?: null;
    }

    public function removeEmptyTranslations(): void
    {
        $optionalLocales = array_diff_key($this->getLocales(), [$this->getDefaultLocale()]);

        foreach ($optionalLocales as $optionalLocale) {
            $this->removeTranslationIfEmpty($optionalLocale);
        }
    }

    private function removeTranslationIfEmpty(string $locale): void
    {
        /* @var $translation EntityTranslationInterface */
        if (!$translation = $this->translate($locale)) {
            return;
        }

        if ($translation->isEmpty()) {
            $this->removeTranslation($translation);
        }
    }

    private function getLocales(): array
    {
        return ['fr', 'en'];
    }

    private function getDefaultLocale(): string
    {
        return 'fr';
    }
}
