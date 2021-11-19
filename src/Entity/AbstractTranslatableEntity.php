<?php

namespace App\Entity;

use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;

abstract class AbstractTranslatableEntity implements TranslatableInterface
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
}
