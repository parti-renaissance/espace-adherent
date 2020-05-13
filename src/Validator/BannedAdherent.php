<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class BannedAdherent extends Constraint
{
    public $message = 'Oups, quelque chose s\'est mal passé';
}
