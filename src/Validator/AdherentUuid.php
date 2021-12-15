<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class AdherentUuid extends Constraint
{
    public $message = 'adherent.uuid.adherent_not_found';
}
