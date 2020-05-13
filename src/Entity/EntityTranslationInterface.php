<?php

namespace App\Entity;

use A2lix\I18nDoctrineBundle\Doctrine\Interfaces\OneLocaleInterface;

interface EntityTranslationInterface extends OneLocaleInterface
{
    public function isEmpty(): bool;
}
