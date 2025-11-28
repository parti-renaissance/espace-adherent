<?php

declare(strict_types=1);

namespace App\Validator\Jecoute;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class NewsTarget extends Constraint
{
    public $undefinedTarget = 'Vous devez choisir entre une notification globale ou sélectionner une zone de segmentation.';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
