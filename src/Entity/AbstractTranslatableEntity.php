<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

abstract class AbstractTranslatableEntity
{
    /**
     * @var EntityTranslationInterface[]|Collection
     *
     * @Assert\Valid
     */
    protected $translations;

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
        /** @var EntityTranslationInterface $translation */
        if (!$translation = $this->translate($locale)) {
            return;
        }

        if ($translation->isEmpty()) {
            $this->removeTranslation($translation);
        }
    }

    /** @return Collection */
    abstract public function getTranslations();

    abstract public function setTranslations(ArrayCollection $translations);

    abstract public function addTranslation($translation);

    abstract public function removeTranslation($translation);
}
