<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class AdherentInterests extends Constraint
{
    public $message = 'Valeur d\'intérêt n\'est pas valide';
}
