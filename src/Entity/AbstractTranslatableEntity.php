<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;

abstract class AbstractTranslatableEntity
{
    protected function getFieldTranslations(string $field): array
    {
        if (!$this->getTranslations()->containsKey('fr')) {
            return [];
        }

        $french = $english = $this->translate('fr');

        if ($this->getTranslations()->containsKey('en')) {
            $english = $this->translate('en');
        }

        $getter = sprintf('get%s', ucfirst($field));

        return [
            'fr' => $french->$getter(),
            'en' => $english->$getter(),
        ];
    }

    /** @return Collection */
    abstract public function getTranslations();

    abstract public function addTranslation($translation);

    abstract public function removeTranslation($translation);

    abstract public function mergeNewTranslations();
}
