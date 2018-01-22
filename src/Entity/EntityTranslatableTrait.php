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

    public function translate(string $locale = 'fr'): ?EntityTranslationInterface
    {
        $translation = $this->translations->filter(
            function (EntityTranslationInterface $translation) use ($locale) {
                return $locale === $translation->getLocale();
            }
        )->first();

        return $translation ?: null;
    }

    public function removeEmptyTranslations(array $optionalLocales): void
    {
        foreach ($optionalLocales as $optionalLocale) {
            $this->removeTranslationIfEmpty($optionalLocale);
        }
    }

    private function removeTranslationIfEmpty(string $locale): void
    {
        /* @var EntityTranslationInterface $translation */
        if (!$translation = $this->translate($locale)) {
            return;
        }

        if ($translation->isEmpty()) {
            $this->removeTranslation($translation);
        }
    }
}
