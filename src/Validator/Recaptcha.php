<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class Recaptcha extends Constraint
{
    public $message = 'common.recaptcha.invalid_message';
    public $service = 'app.validator.recaptcha';

    public function validatedBy()
    {
        return $this->service;
    }
}
