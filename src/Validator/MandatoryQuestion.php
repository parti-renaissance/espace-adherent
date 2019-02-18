<?php

namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class MandatoryQuestion extends Constraint
{
    public $message = 'idea.question.mandatory';
}
