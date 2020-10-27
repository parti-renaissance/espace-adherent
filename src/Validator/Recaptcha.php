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
    public $service = RecaptchaValidator::class;

    public function validatedBy()
    {
        return $this->service;
    }
}
