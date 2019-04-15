<?php

namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class CanBeCoReferent extends Constraint
{
    public $message = 'referent.adherent.can_be_coreferent';
}
