<?php

namespace App\Validator\AdherentFormation;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class FormationContent extends Constraint
{
    public $errorPath = 'file';
    public $missingFileMessage = 'Veuillez télécharger un fichier.';
    public $missingLinkMessage = 'Veuillez spécifier un lien.';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
