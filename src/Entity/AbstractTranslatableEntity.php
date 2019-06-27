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

    protected function getFieldTranslations(string $field): array
    {
        /** @var EntityTranslationInterface $french */
        if (!$french = $this->translate('fr')) {
            return [];
        }

        /** @var EntityTranslationInterface $english */
        if (!$english = $this->translate('en')) {
            $english = $french;
        }

        $getter = sprintf('get%s', ucfirst($field));

        return [
            'fr' => $french->$getter(),
            'en' => $english->$getter(),
        ];
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
