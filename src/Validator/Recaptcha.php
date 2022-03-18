<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class Recaptcha extends Constraint
{
    public $emptyMessage = 'common.recaptcha.empty_message';
    public $message = 'common.recaptcha.invalid_message';
    public $service = RecaptchaValidator::class;

    public function validatedBy()
    {
        return $this->service;
    }
}
