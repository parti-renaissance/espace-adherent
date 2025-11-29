<?php

declare(strict_types=1);

namespace App\Validator\AdherentFormation;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class FormationContent extends Constraint
{
    public $errorPath = 'file';
    public $missingLinkMessage = 'Veuillez spécifier un lien.';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
