<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class AdherentInterests extends Constraint
{
    public $message = 'Valeur d\'intérêt n\'est pas valide';
}
