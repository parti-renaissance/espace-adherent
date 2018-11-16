<?php

namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class BannedAdherent extends Constraint
{
    public $message = 'L\'adresse email "{{ email }}" est bloquée.';
}
