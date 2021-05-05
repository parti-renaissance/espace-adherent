<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class ValidMessageCoalitionsFilter extends Constraint
{
    public $invalidAuthor = 'Vous n\'êtes pas l\'auteur de la cause';
    public $invalidCauseStatus = 'La cause n\'est pas publiée';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
