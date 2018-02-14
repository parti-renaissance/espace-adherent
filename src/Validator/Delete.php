<?php

namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraint;

class Delete extends Constraint
{
    public $message = 'Texte incorrect';
    public $sameText = 'supprimer';
}
