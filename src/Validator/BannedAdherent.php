<?php

namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class BannedAdherent extends Constraint
{
    public $message = 'Cette adresse e-mail est bloquée';
}
